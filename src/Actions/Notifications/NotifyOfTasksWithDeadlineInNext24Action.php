<?php

namespace Xguard\LaravelKanban\Actions\Notifications;

use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Enums\DateTimeFormats;
use Xguard\LaravelKanban\Enums\TaskStatuses;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\TaskVersion;
use Xguard\LaravelKanban\Notifications\TasksWithIncomingDeadline;

class NotifyOfTasksWithDeadlineInNext24Action extends Action
{
    const GLOBAL_APP_TIMEZONE = 'global.appTimezone';
    const SLACK_WEBHOOK_KANYEBAN_DEADLINE_24_H = 'SLACK_WEBHOOK_KANYEBAN_DEADLINE_24H';
    const SLACK = 'slack';

    /**
     * @return mixed
     */
    public function handle()
    {
        $tasks = Task::query()
            ->where(Task::DEADLINE, '>', Carbon::now(config(self::GLOBAL_APP_TIMEZONE))->format(DateTimeFormats::DATE_TIME_FORMAT()->getValue()))
            ->where(Task::DEADLINE, '<=', Carbon::now(config(self::GLOBAL_APP_TIMEZONE))->addHours(24)->format(DateTimeFormats::DATE_TIME_FORMAT()->getValue()))
            ->where(Task::STATUS, TaskStatuses::ACTIVE()->getValue())
            ->with([Task::TASK_VERSION_RELATION_NAME => function ($q) {
                    $q->withTrashed();
                    $q->with([TaskVersion::BOARD_RELATION_NAME => function ($q) {
                        $q->withTrashed();
                    }]);
            }])
            ->get();

        if ($tasks->count() > 0) {
            \Notification::route(self::SLACK, env(self::SLACK_WEBHOOK_KANYEBAN_DEADLINE_24_H))
                ->notify(new TasksWithIncomingDeadline($tasks));
        }
    }
}
