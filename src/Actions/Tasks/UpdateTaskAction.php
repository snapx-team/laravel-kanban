<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Carbon;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Actions\Badges\CreateBadgeAction;
use Xguard\LaravelKanban\Actions\ErpShareables\UpdateErpShareablesDescriptionAction;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class UpdateTaskAction extends Action
{
    public function authorize()
    {
        return AccessManager::canAccessBoardUsingTaskId($this->taskId);
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'assignedTo' => ['nullable', 'array'],
            'badge' => ['present', 'array'],
            'columnId' => ['nullable', 'integer', 'gt:0'],
            'deadline' => ['required', 'string'],
            'description' => ['required', 'string'],
            'erpContracts' => ['present', 'array'],
            'erpEmployees' => ['present', 'array'],
            'name' => ['required', 'string'],
            'rowId' => ['nullable', 'integer', 'gt:0'],
            'status' => ['nullable', 'string'],
            'taskId' => ['required', 'integer', 'gt:0'],
            'timeEstimate' => ['present', 'integer', 'digits_between: 0,40'],
            //files
            'task_files'=>  ['nullable', 'array'],
            'added_task_files'=>  ['nullable', 'array'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();
            $task = Task::find($this->taskId);
            $prevTask = clone $task;
            $prevGroup = $prevTask->shared_task_data_id;

            //find or create badge
            $badgeName = count($this->badge) > 0 ? trim($this->badge['name']) : '--';
            $badge = Badge::where('name', $badgeName)->first();

            if (!$badge) {
                $badge = app(CreateBadgeAction::class)->fill(["name" => $badgeName])->run();
            }

            app(SyncAssignedEmployeesToTaskAction::class)->fill(["assignedTo" => $this->assignedTo, "task" => $task])->run();

            if ($this->sharedTaskDataId === $prevGroup) {
                // update shared data if group hasn't changed
                app(UpdateErpShareablesDescriptionAction::class)->fill([
                    "description" => $this->description,
                    "sharedTaskDataId" => $this->sharedTaskDataId,
                    "erpEmployees" => $this->erpEmployees,
                    "erpContracts" => $this->erpContracts,
                ])->run();
            } else {
                // delete previous group if no other tasks point to it
                $tasksWithSharedTaskDataCount = Task::where('shared_task_data_id', $prevGroup)->count();
                if ($tasksWithSharedTaskDataCount === 0) {
                    SharedTaskData::where('id', $prevGroup)->delete();
                }
            }

            $task->update([
                'name' => $this->name,
                'deadline' => Carbon::parse($this->deadline),
                'badge_id' => $badge->id,
                'column_id' => $this->columnId,
                'row_id' => $this->rowId,
                'shared_task_data_id' => $this->sharedTaskDataId,
                'time_estimate' => $this->timeEstimate
            ]);

            if ($this->status !== $prevTask->status) {
                app(UpdateTaskStatusAction::class)->fill([
                    'taskId' => $task->id,
                    'newStatus' => $this->status
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
                    "[" . implode("','", array_keys($difference)) . "] was updated.",
                    null,
                    $task['id'],
                    'Xguard\LaravelKanban\Models\Task'
                );
                TasksRepository::versionTask([
                    "index" => $task->index,
                    "name" => $task->name,
                    "deadline" => $task->deadline,
                    "shared_task_data_id" => $task->shared_task_data_id,
                    "reporter_id" => $task->reporter_id,
                    "column_id" => $task->column_id,
                    "row_id" => $task->row_id,
                    "board_id" => $task->board_id,
                    "badge_id" => $task->badge_id,
                    "status" => $task->status ? $task->status : 'active',
                    "task_id" => $task->id,
                    "log_id" => $log->id,
                    'time_estimate' => $this->timeEstimate
                ]);
            }
            app(UpdateTaskFilesActions::class)->fill(['contract' => $task, 'taskFiles' => $this->task_files, 'addedTaskFiles' => $this->added_task_files])->run();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
