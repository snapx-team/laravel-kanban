<?php

namespace Xguard\LaravelKanban\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Xguard\LaravelKanban\Models\Task;

class TasksWithIncomingDeadline extends Notification
{
    use Queueable;

    /** @var Task[] $tasks */
    protected $tasks;

    /**
     * Create a new notification instance.
     *
     * @param JobSiteShift $jobSiteShift
     * @return void
     */
    public function __construct($tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        $message = (new SlackMessage)
            ->success()
            ->content('There are tasks with deadline in the next 24 hours!');
        foreach ($this->tasks as $task) {
            $message->attachment(function ($attachment) use ($task) {
                $fields = [
                    'Task' => $task->name,
                    'Board' => $task->board->name,
                    'Deadline' => Carbon::parse($task->deadline)->format('Y-m-d H:i:s'),
                ];
                $attachment->fields($fields);
            });
        }
        return $message;
    }
}
