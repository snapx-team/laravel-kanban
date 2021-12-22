<?php

namespace Xguard\LaravelKanban\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;

class NotificationsRepository
{
    public static function getNotificationData(int $userId, $logType): Collection
    {
        $employee = Employee::where('user_id', '=', $userId)
        ->with('notifications.user')
        ->with(['notifications' => function ($q) use ($logType) {
            if ($logType !== "null") {
                $q->where('log_type', (int)$logType);
            }
            $q->with('targetEmployee.user')
                ->with(['taskVersion' => function ($q) {
                    $q->withTaskData();
                }])
                ->orderBy('created_at', 'desc')
                ->with(['loggable' => function (MorphTo $morphTo) {
                    $morphTo->withoutGlobalScope(SoftDeletingScope::class)
                        ->morphWith([
                            Task::class => ['board', 'row', 'column'],
                            Comment::class => ['task.board', 'task.row', 'task.column'],
                            Board::class]);
                }])->distinct()->paginate(10);
        }])->first();

        return $employee->notifications;
    }
}
