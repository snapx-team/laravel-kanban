<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class UpdateTaskColumnAndRowAction extends Action
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
            'columnId' => ['required', 'integer', 'gt:0'],
            'rowId' => ['required', 'integer', 'gt:0'],
            'taskId' => ['required', 'integer', 'gt:0'],
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
            $task = Task::findOrFail($this->taskId);
            $prevTask = clone $task;
            $task->update(['column_id' => $this->columnId, 'row_id' => $this->rowId]);

            $log = Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_MOVED,
                'Task [' . $task->task_simple_name . '] moved from [' . $prevTask->row->name . ':' .  $prevTask->column->name . '] to [' . $task->row->name . ':' . $task->column->name . ']',
                null,
                $task->id,
                'Xguard\LaravelKanban\Models\Task'
            );
            TasksRepository::versionTask([
                "index" => $task->index,
                "name" => $task->name,
                "deadline" => $task->deadline,
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
