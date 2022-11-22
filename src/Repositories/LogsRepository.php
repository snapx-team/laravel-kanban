<?php

namespace Xguard\LaravelKanban\Repositories;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Comment;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

class LogsRepository
{
    public static function create(array $payload): Log
    {
        return Log::create($payload);
    }

    // this will also get all logs of related tasks
    public static function getLogs($taskId)
    {
        $sharedTaskData = Task::find($taskId);

        if ($sharedTaskData) {
            $sharedTaskDataId = $sharedTaskData->shared_task_data_id;
            $allAssociatedTasks = Task::where(Task::SHARED_TASK_DATA_RELATION_ID, '=', $sharedTaskDataId)->pluck(Task::ID)->toArray();
            return Log::with([Log::USER_RELATION_NAME, Log::LOGGABLE_RELATION_NAME => function (MorphTo $morphTo) {
                $morphTo->morphWith([Task::class => [Task::BOARD_RELATION_NAME, Task::BADGE_RELATION_NAME]]);
            }])
                ->with(Log::TARGET_EMPLOYEE_RELATION_NAME.'.'.Employee::USER_RELATION_NAME)
                ->with([Log::TASK_VERSION_RELATION_NAME => function ($q) {
                    $q->withTaskData();
                }])
                ->orderBy(Log::CREATED_AT, 'desc')
                ->with([Log::LOGGABLE_RELATION_NAME => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Task::class => [Task::BOARD_RELATION_NAME, Task::BADGE_RELATION_NAME, Task::COLUMN_RELATION_NAME],
                        Comment::class => [
                            Comment::TASK_RELATION_NAME.'.'.Task::BOARD_RELATION_NAME,
                            Comment::TASK_RELATION_NAME.'.'.Task::ROW_RELATION_NAME,
                            Comment::TASK_RELATION_NAME.'.'.Task::COLUMN_RELATION_NAME],
                        Board::class
                    ]);
                }])
                ->where(Log::LOGGABLE_TYPE, LoggableTypes::TASK()->getValue())
                ->whereIn(Log::LOGGABLE_ID, $allAssociatedTasks)
                ->distinct()->paginate(10);
        }
        return null;
    }
}
