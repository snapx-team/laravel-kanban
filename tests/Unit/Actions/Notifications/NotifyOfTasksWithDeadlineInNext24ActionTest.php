<?php

namespace Tests\Unit\Actions\Notifications;

use App\Helpers\DateTimeHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Notifications\NotifyOfTasksWithDeadlineInNext24Action;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Notifications\TasksWithIncomingDeadline;

class NotifyOfTasksWithDeadlineInNext24ActionTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        Auth::setUser($user);

        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $user->id,]);
        session(['role' => 'admin', 'employee_id' => $employee->id]);

        Carbon::setTestNow(DateTimeHelper::parse('2021-01-01')->toDateTimeString());
        $this->now = DateTimeHelper::now();
    }

    public function testNotifyOfTasksWithDeadlineInNext24ActionSendsNotification()
    {
        Notification::fake();

        factory(Task::class)->create(['deadline' => $this->now->copy()->subHours(rand(0, 10))->toDateTimeString()]);
        
        for ($i = 0; $i < 48; $i += 4) {
            factory(Task::class)->create(['deadline' => $this->now->copy()->addHours($i)->toDateTimeString()]);
        }

        app(NotifyOfTasksWithDeadlineInNext24Action::class)->run();

        Notification::assertSentTo(new AnonymousNotifiable(), TasksWithIncomingDeadline::class, function ($notification, $channels, $notifiable) {
            return $notification->getTasks()->count() == 6;
        });
    }

    public function testNotifyOfTasksWithDeadlineInNext24ActionSendsNoNotificationWithNoTasksWithDeadlineInNext24h()
    {
        Notification::fake();

        for ($i = 25; $i < 48; $i += 4) {
            factory(Task::class)->create(['deadline' => $this->now->copy()->addHours($i)->toDateTimeString()]);
        }

        for ($i = 0; $i < 24; $i += 4) {
            factory(Task::class)->create(['deadline' => $this->now->copy()->subHours($i)->toDateTimeString()]);
        }

        app(NotifyOfTasksWithDeadlineInNext24Action::class)->run();

        Notification::assertNothingSent();
    }
}
