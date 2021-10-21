<?php

namespace Xguard\LaravelKanban\Actions\Notifications;

use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Notifications\TasksWithIncomingDeadline;

class NotifyOfTasksWithDeadlineInNext24Action extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $tasks = Task::query()
                ->where('deadline', '>', Carbon::now(config('global.appTimezone'))->format('Y-m-d H:i:s'))
                ->where('deadline', '<=', Carbon::now(config('global.appTimezone'))->addHours(24)->format('Y-m-d H:i:s'))
                ->where('status', 'active')
                ->get();

        if ($tasks->count() > 0) {
            \Notification::route('slack', env('SLACK_WEBHOOK_KANYEBAN_DEADLINE-24H'))
                ->notify(new TasksWithIncomingDeadline($tasks));
        }
    }
}
