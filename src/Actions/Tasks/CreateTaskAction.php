<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Carbon;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Throwable;
use Xguard\LaravelKanban\Actions\Badges\CreateBadgeAction;
use Xguard\LaravelKanban\Actions\ErpShareables\CreateErpShareablesAction;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Enums\SessionVariables;
use Xguard\LaravelKanban\Enums\TaskStatuses;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class CreateTaskAction extends Action
{
    const ASSIGNED_TO = 'assignedTo';
    const ASSOCIATED_TASK = 'associatedTask';
    const BADGE = 'badge';
    const COLUMN_ID = 'columnId';
    const DEADLINE = 'deadline';
    const DESCRIPTION = 'description';
    const ERP_CONTRACTS = 'erpContracts';
    const ERP_EMPLOYEES = 'erpEmployees';
    const NAME = 'name';
    const ROW_ID = 'rowId';
    const SELECTED_KANBANS = 'selectedKanbans';
    const TIME_ESTIMATE = 'timeEstimate';
    const TASK_FILES = 'taskFiles';
    const FILES_TO_UPLOAD = 'filesToUpload';

    public function rules(): array
    {
        return [
            self::ASSIGNED_TO => ['nullable', 'array'],
            self::ASSOCIATED_TASK => ['nullable', 'array'],
            self::BADGE => ['present', 'array'],
            self::COLUMN_ID => ['nullable', 'integer', 'gt:0'],
            self::DEADLINE => ['required', 'string'],
            self::DESCRIPTION => ['required', 'string'],
            self::ERP_CONTRACTS => ['present', 'array'],
            self::ERP_EMPLOYEES => ['present', 'array'],
            self::NAME => ['required', 'string'],
            self::ROW_ID => ['nullable', 'integer', 'gt:0'],
            self::SELECTED_KANBANS => ['required', 'array', "min:1"],
            self::TIME_ESTIMATE => ['present', 'integer', 'digits_between: 0,40'],
            self::TASK_FILES => ['nullable', 'array'],
            self::FILES_TO_UPLOAD => ['nullable', 'array'],
        ];
    }

    public function messages()
    {
        return [
            'selectedKanbans.min' => 'You need to select at least one board',
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
            $badgeName = count($this->badge) > 0 ? trim($this->badge[self::NAME]) : '--';
            $badge = Badge::where(self::NAME, $badgeName)->first();

            if (!$badge) {
                $badge = app(CreateBadgeAction::class)->fill([self::NAME => $badgeName])->run();
            }

            if ($this->associatedTask !== null) {
                $sharedTaskDataId = $this->associatedTask[Task::SHARED_TASK_DATA_RELATION_ID];
            } else {
                $sharedTaskDataId = (app(CreateErpShareablesAction::class)->fill([
                    CreateErpShareablesAction::DESCRIPTION => $this->description,
                    CreateErpShareablesAction::ERP_EMPLOYEES => $this->erpEmployees,
                    CreateErpShareablesAction::ERP_CONTRACTS => $this->erpContracts,
                ])->run())->id;
            }

            $maxIndex = $this->columnId ? Task::where(Task::COLUMN_ID, $this->columnId)->where(
                Task::STATUS,
                TaskStatuses::ACTIVE()->getValue()
            )->max(Task::INDEX) + 1 : null;

            foreach ($this->selectedKanbans as $kanban) {
                $task = Task::create([
                    Task::INDEX => $maxIndex,
                    Task::REPORTER_ID => session(SessionVariables::EMPLOYEE_ID()->getValue()),
                    Task::NAME => $this->name,
                    Task::DEADLINE => Carbon::parse($this->deadline),
                    Task::BADGE_ID => $badge->id,
                    Task::COLUMN_ID => $this->columnId ?: null,
                    Task::ROW_ID => $this->rowId ?: null,
                    Task::BOARD_ID => $kanban['id'],
                    Task::SHARED_TASK_DATA_RELATION_ID => $sharedTaskDataId,
                    Task::TIME_ESTIMATE => $this->timeEstimate
                ]);

                app(SyncAssignedEmployeesToTaskAction::class)->fill([
                    SyncAssignedEmployeesToTaskAction::ASSIGNED_TO => $this->assignedTo,
                    SyncAssignedEmployeesToTaskAction::TASK => $task
                ])->run();

                $log = Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_CREATED,
                    'Created new task ['.$task->task_simple_name.'] in board ['.$task->board->name.']',
                    null,
                    $task->id,
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

                app(UpdateTaskFilesAction::class)->fill([
                    UpdateTaskFilesAction::TASK => $task, UpdateTaskFilesAction::TASK_FILES => $this->taskFiles, UpdateTaskFilesAction::FILES_TO_UPLOAD => $this->filesToUpload
                ])->run();
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
