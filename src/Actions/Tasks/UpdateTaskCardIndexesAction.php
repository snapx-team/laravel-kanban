<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Throwable;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class UpdateTaskCardIndexesAction extends Action
{
    /**
     * Execute the action and return a result.
     *
     * @return void
     * @throws Throwable
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();

            $newIndex = 0;
            foreach ($this->taskCards as $taskCard) {
                $task = Task::find($taskCard['id']);
                $task->update(['index' => $newIndex]);
                $task->refresh();
                $newIndex++;

                $log = Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_CHANGED_INDEX,
                    "Task [".$task->task_simple_name."] moved to index [".$task->index."]",
                    null,
                    $task->id,
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
                    "status" => $task->status ?: 'active',
                    "task_id" => $task->id,
                    "log_id" => $log->id,
                    'time_estimate' => $task->time_estimate,
                ]);
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
