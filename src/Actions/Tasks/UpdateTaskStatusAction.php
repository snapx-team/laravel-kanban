<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Exception;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Throwable;
use Xguard\LaravelKanban\Enums\TaskStatuses;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class UpdateTaskStatusAction extends Action
{
    const TASK_ID = 'taskId';
    const NEW_STATUS = 'newStatus';

    public function authorize()
    {
        return AccessManager::canAccessBoardUsingTaskId($this->taskId);
    }

    public function rules(): array
    {
        return [
            self::TASK_ID => ['required', 'integer', 'gt:0'],
            self::NEW_STATUS => ['required', 'string']
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

            if (!in_array($this->newStatus, [TaskStatuses::ACTIVE()->getValue(), TaskStatuses::COMPLETED()->getValue(), TaskStatuses::CANCELLED()->getValue()])) {
                throw new Exception("invalid new status");
            }

            if ($this->newStatus === TaskStatuses::ACTIVE()->getValue()) {
                $task->update([
                    Task::STATUS => $this->newStatus,
                ]);
            } else {
                $task->update([
                    Task::STATUS => $this->newStatus,
                    Task::ROW_ID => null,
                    Task::COLUMN_ID => null,
                    Task::INDEX => null
                ]);
            }

            if ($this->newStatus === TaskStatuses::COMPLETED()->getValue()) {
                $logType = Log::TYPE_CARD_COMPLETED;
                $logDescription =  'Task [' . $task->task_simple_name . '] in board [' . $task->board->name . '] set to completed';
            } elseif ($this->newStatus === TaskStatuses::CANCELLED()->getValue()) {
                $logType = Log::TYPE_CARD_CANCELLED;
                $logDescription = 'Task [' . $task->task_simple_name . '] in board [' . $task->board->name . '] set to cancelled';
            } elseif ($this->newStatus === TaskStatuses::ACTIVE()->getValue()) {
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
