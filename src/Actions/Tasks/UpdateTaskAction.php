<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Carbon;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Throwable;
use Xguard\LaravelKanban\Actions\Badges\CreateBadgeAction;
use Xguard\LaravelKanban\Actions\ErpShareables\UpdateErpShareablesDescriptionAction;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class UpdateTaskAction extends Action
{
    const ASSIGNED_TO = 'assignedTo';
    const BADGE = 'badge';
    const COLUMN_ID = 'columnId';
    const DEADLINE = 'deadline';
    const DESCRIPTION = 'description';
    const ERP_CONTRACTS = 'erpContracts';
    const ERP_EMPLOYEES = 'erpEmployees';
    const ROW_ID = 'rowId';
    const NAME = 'name';
    const TASK_ID = 'taskId';
    const TIME_ESTIMATE = 'timeEstimate';
    const TASK_FILES = 'taskFiles';
    const FILES_TO_UPLOAD = 'filesToUpload';
    const STATUS = 'status';
    const SHARED_TASK_DATA_ID = 'sharedTaskDataId';

    public function authorize()
    {
        return AccessManager::canAccessBoardUsingTaskId($this->taskId);
    }

    public function rules(): array
    {
        return [
            self::ASSIGNED_TO => ['nullable', 'array'],
            self::BADGE => ['present', 'array'],
            self::COLUMN_ID => ['nullable', 'integer', 'gt:0'],
            self::DEADLINE => ['required', 'string'],
            self::DESCRIPTION => ['required', 'string'],
            self::ERP_CONTRACTS => ['present', 'array'],
            self::ERP_EMPLOYEES => ['present', 'array'],
            self::NAME => ['required', 'string'],
            self::ROW_ID => ['nullable', 'integer', 'gt:0'],
            self::TASK_ID => ['required', 'integer', 'gt:0'],
            self::TIME_ESTIMATE => ['present', 'integer', 'digits_between: 0,40'],
            self::TASK_FILES => ['nullable', 'array'],
            self::FILES_TO_UPLOAD => ['nullable', 'array'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required',
        ];
    }

    /**
     * @throws Throwable
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();
            $task = Task::find($this->taskId);
            $prevTask = clone $task;
            $prevGroup = $prevTask->shared_task_data_id;

            //find or create badge
            $badgeName = count($this->badge) > 0 ? trim($this->badge[Badge::NAME]) : '--';
            $badge = Badge::where(Badge::NAME, $badgeName)->first();

            if (!$badge) {
                $badge = app(CreateBadgeAction::class)->fill([CreateBadgeAction::NAME => $badgeName])->run();
            }

            app(SyncAssignedEmployeesToTaskAction::class)->fill([
                SyncAssignedEmployeesToTaskAction::ASSIGNED_TO => $this->assignedTo,
                SyncAssignedEmployeesToTaskAction::TASK => $task
            ])->run();

            if ($this->sharedTaskDataId === $prevGroup) {
                // update shared data if group hasn't changed
                app(UpdateErpShareablesDescriptionAction::class)->fill([
                    UpdateErpShareablesDescriptionAction::DESCRIPTION => $this->description,
                    UpdateErpShareablesDescriptionAction::SHARED_TASK_DATA_ID => $this->sharedTaskDataId,
                    UpdateErpShareablesDescriptionAction::ERP_EMPLOYEES => $this->erpEmployees,
                    UpdateErpShareablesDescriptionAction::ERP_CONTRACTS => $this->erpContracts,
                ])->run();
            } else {
                // delete previous group if no other tasks point to it
                $tasksWithSharedTaskDataCount = Task::where(Task::SHARED_TASK_DATA_RELATION_ID, $prevGroup)->count();
                if ($tasksWithSharedTaskDataCount === 0) {
                    SharedTaskData::where(SharedTaskData::ID, $prevGroup)->delete();
                }
            }

            $task->update([
                Task::NAME => $this->name,
                Task::DEADLINE => Carbon::parse($this->deadline),
                Task::BADGE_ID => $badge->id,
                Task::COLUMN_ID => $this->columnId,
                Task::ROW_ID => $this->rowId,
                Task::SHARED_TASK_DATA_RELATION_ID => $this->sharedTaskDataId,
                Task::TIME_ESTIMATE => $this->timeEstimate
            ]);

            if ($this->status !== $prevTask->status) {
                app(UpdateTaskStatusAction::class)->fill([
                    UpdateTaskStatusAction::TASK_ID => $task->id,
                    UpdateTaskStatusAction::NEW_STATUS => $this->status
                ])->run();
            }

            // logic to log what was changed during update

            $ignoreColumns = array_flip(['created_at', 'updated_at', 'hours_to_deadline', 'task_simple_name', 'board']);
            $keys = array_filter(array_keys($task->toArray()), function ($key) use ($ignoreColumns) {
                return !array_key_exists($key, $ignoreColumns);
            });
            $currentData = [];
            $previousData = [];
            foreach ($keys as $key) {
                $currentData[$key] = $task[$key];
                $previousData[$key] = $prevTask[$key];
            }
            $difference = array_diff($previousData, $currentData);

            // end logic to log what was changed during update

            if (count($difference) > 0) {
                $log = Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_UPDATED,
                    "[".implode("','", array_keys($difference))."] was updated.",
                    null,
                    $task['id'],
                    LoggableTypes::TASK()->getValue()
                );
                TasksRepository::versionTask([
                    TasksRepository::INDEX => $task->index,
                    TasksRepository::NAME => $task->name,
                    TasksRepository::DEADLINE => $task->deadline,
                    TasksRepository::SHARED_TASK_DATA_ID => $task->shared_task_data_id,
                    TasksRepository::REPORTER_ID => $task->reporter_id,
                    TasksRepository::COLUMN_ID => $task->column_id,
                    TasksRepository::ROW_ID => $task->row_id,
                    TasksRepository::BOARD_ID => $task->board_id,
                    TasksRepository::BADGE_ID => $task->badge_id,
                    TasksRepository::STATUS => $task->status ?: 'active',
                    TasksRepository::TASK_ID => $task->id,
                    TasksRepository::LOG_ID => $log->id,
                    TasksRepository::TIME_ESTIMATE => $this->timeEstimate
                ]);
            }

            app(UpdateTaskFilesAction::class)->fill([
                UpdateTaskFilesAction::TASK => $task, UpdateTaskFilesAction::TASK_FILES => $this->taskFiles,
                UpdateTaskFilesAction::FILES_TO_UPLOAD => $this->filesToUpload
            ])->run();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
