<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use DB;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Throwable;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class RemoveTaskFromGroupAction extends Action
{
    const TASK_ID = 'taskId';

    public function rules(): array
    {
        return [
            self::TASK_ID => ['required', 'integer', 'gt:0'],
        ];
    }

    /**
     * @throws Throwable
     */
    public function handle()
    {
        try {
            DB::beginTransaction();
            $task = Task::findOrFail($this->taskId);
            $sharedTaskData = SharedTaskData::create([SharedTaskData::DESCRIPTION => $task['sharedTaskData']['description']]);
            $task->update([
                Task::SHARED_TASK_DATA_RELATION_ID => $sharedTaskData->id,
            ]);
            $log = Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_REMOVED_FROM_GROUP,
                'Task [' . $task->task_simple_name . '] was removed from group',
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
                TasksRepository::TIME_ESTIMATE => $task->time_estimate
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
