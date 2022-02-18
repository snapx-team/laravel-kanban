<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Throwable;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class UpdateGroupAction extends Action
{
    const TASK_ID = 'taskId';
    const GROUP_ID = 'groupId';

    public function rules(): array
    {
        return [
            self::TASK_ID => ['required', 'integer', 'gt:0'],
            self::GROUP_ID => ['required', 'integer', 'gt:0']
        ];
    }

    /**
     * @throws Throwable
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();
            $task = Task::findOrFail($this->taskId);
            $prevGroup = (clone $task)->shared_task_data_id;


            $task->update([
                Task::SHARED_TASK_DATA_RELATION_ID => $this->groupId,
            ]);

            // delete previous group if no other tasks point to it
            $tasksWithSharedTaskData = Task::where(Task::SHARED_TASK_DATA_RELATION_ID, $prevGroup)->count();
            if ($tasksWithSharedTaskData === 0) {
                SharedTaskData::where(SharedTaskData::ID, $prevGroup)->delete();
            }

            $log = Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_ASSIGNED_GROUP,
                'Task ['.$task->task_simple_name.'] changed group',
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
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
