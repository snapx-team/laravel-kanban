<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class UpdateTaskStatusAction extends Action
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
            'taskId' => ['required', 'integer', 'gt:0'],
            'newStatus' => ['required', 'string']
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
            if ($this->newStatus === 'active') {
                $task->update([
                    'status' => $this->newStatus,
                ]);
            } else {
                $task->update([
                    'status' => $this->newStatus,
                    'row_id' => null,
                    'column_id' => null,
                    'index' => null
                ]);
            }

            if ($this->newStatus === 'completed') {
                $logType = Log::TYPE_CARD_COMPLETED;
                $logDescription =  'Task [' . $task->task_simple_name . '] in board [' . $task->board->name . '] set to completed';
            } elseif ($this->newStatus === 'cancelled') {
                $logType = Log::TYPE_CARD_CANCELED;
                $logDescription = 'Task [' . $task->task_simple_name . '] in board [' . $task->board->name . '] set to cancelled';
            } elseif ($this->newStatus === 'active') {
                $logType = Log::TYPE_CARD_ACTIVATED;
                $logDescription = 'Task [' . $task->task_simple_name . '] in board [' . $task->board->name . '] set to active';
            }
            $log = Log::createLog(
                Auth::user()->id,
                $logType,
                $logDescription,
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
