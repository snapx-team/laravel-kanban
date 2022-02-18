<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Throwable;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class UpdateTaskColumnAndRowAction extends Action
{
    const COLUMN_ID = 'columnId';
    const ROW_ID = 'rowId';
    const TASK_ID = 'taskId';

    public function authorize()
    {
        return AccessManager::canAccessBoardUsingTaskId($this->taskId);
    }

    public function rules(): array
    {
        return [
            self::COLUMN_ID => ['required', 'integer', 'gt:0'],
            self::ROW_ID => ['required', 'integer', 'gt:0'],
            self::TASK_ID => ['required', 'integer', 'gt:0'],
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
            $prevTask = clone $task;
            $task->update([Task::COLUMN_ID => $this->columnId, Task::ROW_ID => $this->rowId]);

            $log = Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_MOVED,
                'Task ['.$task->task_simple_name.'] moved from ['.$prevTask->row->name.':'.$prevTask->column->name.'] to ['.$task->row->name.':'.$task->column->name.']',
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
