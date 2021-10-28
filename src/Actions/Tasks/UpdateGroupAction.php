<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class UpdateGroupAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'taskId' => ['required', 'integer', 'gt:0'],
            'groupId' => ['required', 'string']
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
            $prevGroup = (clone $task)->shared_task_data_id;


            $task->update([
                'shared_task_data_id' => $this->groupId,
            ]);

            // delete previous group if no other tasks point to it
            $tasksWithSharedTaskData = Task::where('shared_task_data_id', $prevGroup)->count();
            if ($tasksWithSharedTaskData === 0) {
                SharedTaskData::where('id', $prevGroup)->delete();
            }

            $log = Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_ASSIGNED_GROUP,
                'Task [' . $task->task_simple_name . '] changed group',
                null,
                $task->id,
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
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
