<?php

namespace Xguard\LaravelKanban\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Xguard\LaravelKanban\Enums\DateTimeFormats;
use Xguard\LaravelKanban\Models\Task;

class TasksWithIncomingDeadline extends Notification
{
    use Queueable;

    /** @var Task[] $tasks */
    protected $tasks;

    /**
     * Create a new notification instance.
     *
     * @param $tasks
     */
    public function __construct($tasks)
    {
        $this->tasks = $tasks;
    }

    public function getTasks()
    {
        return $this->tasks;
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

                $boardName = 'In Backlog';
                if ($task->taskVersion->boardID) {
                    $boardName = $task->taskVersion->board->name;
                }

                $fields = [
                    'ID' => $task->task_simple_name,
                    'Task Name' => $task->taskVersion->name,
                    'Board Name' => $boardName,
                    'Deadline' => Carbon::parse($task->deadline)->format(DateTimeFormats::DATE_TIME_FORMAT()->getValue()),
                    'Time Left' => $task->hours_to_deadline . ' Hours',
                    'URL' => env('APP_URL') .'/kanban/board?name=board&id='. $task->taskVersion->board_id .'&task=' . $task->taskVersion->task_id
                ];
                $attachment->fields($fields);
            });
        }
        return $message;
    }
}
