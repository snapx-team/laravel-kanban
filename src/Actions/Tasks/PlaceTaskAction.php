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

class PlaceTaskAction extends Action
{
    const TASK_ID = 'taskId';
    const BOARD_ID = 'boardId';
    const ROW_ID = 'rowId';
    const COLUMN_ID = 'columnId';

    public function authorize(): bool
    {
        return AccessManager::canAccessBoardUsingTaskId($this->taskId);
    }

    public function rules(): array
    {
        return [
            self::TASK_ID => ['required', 'integer', 'gt:0'],
            self::BOARD_ID => ['required', 'integer', 'gt:0'],
            self::ROW_ID => ['nullable' , 'required_with:columnId,', 'integer', 'gt:0'],
            self::COLUMN_ID => ['nullable', 'required_with:rowId', 'integer', 'gt:0'],
        ];
    }

    /**
     * @throws Throwable
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();
            $task = Task::find($this->taskId);
            $task->update([
                Task::BOARD_ID => $this->boardId,
                Task::ROW_ID => $this->rowId,
                Task::COLUMN_ID => $this->columnId
            ]);

            // occurs when users wants to remove card that is currently placed in board
            if ($this->rowId === null && $this->columnId === null) {
                $logDesc = 'Task ['.$task->task_simple_name.'] placed in board ['.$task->board->name.'] without a row and column ';
            } else {
                $logDesc = 'Task ['.$task->task_simple_name.'] placed in board ['.$task->board->name.'] on ['.$task->row->name.':'.$task->column->name.']';
            }
            $log = Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_PLACED,
                $logDesc,
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
