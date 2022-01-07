<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use DB;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class RemoveTaskFromGroupAction extends Action
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
            DB::beginTransaction();
            $taskCard = Task::findOrFail($this->taskId);

            $sharedTaskData = SharedTaskData::create(['description' => $taskCard['sharedTaskData']['description']]);

            $taskCard->update([
                'shared_task_data_id' => $sharedTaskData->id,
            ]);

            $log = Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_REMOVED_FROM_GROUP,
                'Task [' . $taskCard->task_simple_name . '] was removed from group',
                null,
                $taskCard->id,
                'Xguard\LaravelKanban\Models\Task'
            );
            TasksRepository::versionTask([
                "index" => $taskCard->index,
                "name" => $taskCard->name,
                "deadline" => $taskCard->deadline,
                "shared_task_data_id" =>$taskCard->shared_task_data_id,
                "reporter_id" => $taskCard->reporter_id,
                "column_id" => $taskCard->column_id,
                "row_id" => $taskCard->row_id,
                "board_id" => $taskCard->board_id,
                "badge_id" => $taskCard->badge_id,
                "status" => $taskCard->status ? $taskCard->status : 'active',
                "task_id" => $taskCard->id,
                "log_id" => $log->id
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
