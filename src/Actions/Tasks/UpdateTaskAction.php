<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Actions\ErpShareables\UpdateErpShareablesDescriptionAction;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\Employee;
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
            'sharedTaskDataId' => ['required', 'integer', 'gt:0'],
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

            //change who's assigned to the task if necessary
            if ($this->assignedTo !== null) {
                $employeeArray = [];
                foreach ($this->assignedTo as $employee) {
                    array_push($employeeArray, $employee['employee']['id']);
                }

                $assignedToResponse = $task->assignedTo()->sync($employeeArray);

                foreach ($assignedToResponse['attached'] as $employeeId) {
                    $employee = Employee::find($employeeId);
                    $log = Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_CARD_ASSIGNED_TO_USER,
                        'User ' . $employee->user->full_name . ' has been assigned to task [' . $task->task_simple_name . ']',
                        $employee->id,
                        $task->id,
                        'Xguard\LaravelKanban\Models\Task'
                    );
                }

                foreach ($assignedToResponse['detached'] as $employeeId) {
                    $employee = Employee::find($employeeId);
                    $log = Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_CARD_UNASSIGNED_TO_USER,
                        'User ' . $employee->user->full_name . ' has been removed from task [' . $task->task_simple_name . ']',
                        $employee->id,
                        $task->id,
                        'Xguard\LaravelKanban\Models\Task'
                    );
                }
            }

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
                'status' => $this->status,
                'deadline' => date('y-m-d h:m', strtotime($this->deadline)),
                'badge_id' => $badge->id,
                'column_id' => $this->columnId,
                'row_id' => $this->rowId,
                'shared_task_data_id' => $this->sharedTaskDataId
            ]);

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
                    "deadline" => date('y-m-d h:m', strtotime($task->deadline)),
                    "shared_task_data_id" =>$task->shared_task_data_id,
                    "reporter_id" => $task->reporter_id,
                    "column_id" => $task->column_id,
                    "row_id" => $task->row_id,
                    "board_id" => $task->board_id,
                    "badge_id" => $task->badge_id,
                    "status" => $task->status ? $task->status : 'active',
                    "task_id" => $task->id,
                    "log_id" => $log->id
                ]);
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
