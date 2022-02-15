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
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
