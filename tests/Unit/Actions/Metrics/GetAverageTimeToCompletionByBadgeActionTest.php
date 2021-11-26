<?php

namespace Tests\Unit;

use App\Models\User;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Metrics\GetAverageTimeToCompletionByBadgeAction;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

class GetAverageTimeToCompletionByBadgeActionTest extends TestCase
{

    use WithFaker, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        Auth::setUser($user);
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $user->id,]);
        session(['role' => 'admin', 'employee_id' => $employee->id]);
    }

    public function testAverageTimeToCompletionByBadgeRetrievesAccurateData()
    {
        $badge = factory(Badge::class)->create();
        $task1 = factory(Task::class)->create(['badge_id'=>$badge->id]);
        $task2 = factory(Task::class)->create(['badge_id'=>$badge->id]);
        $tasks = [$task1, $task2];

        $end = new DateTime('now');
        $start = new DateTime('yesterday');
        $twoHours = new DateTime('2 hours ago');
        $eightHours = new DateTime('8 hours ago');

        factory(Log::class)->create([
            'user_id' => factory(User::class),
            'log_type' => LOG::TYPE_CARD_ASSIGNED_TO_USER,
            'targeted_employee_id' => factory(Employee::class),
            'loggable_id' => $task1->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task',
            'created_at' => $twoHours->format('Y-m-d H:i:s')
        ]);

        factory(Log::class)->create([
            'user_id' => factory(User::class),
            'log_type' => LOG::TYPE_CARD_ASSIGNED_TO_USER,
            'targeted_employee_id' => factory(Employee::class),
            'loggable_id' => $task2->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task',
            'created_at' => $eightHours->format('Y-m-d H:i:s')
        ]);

        foreach ($tasks as $task) {
            factory(Log::class)->create([
                'user_id' => factory(User::class),
                'log_type' => LOG::TYPE_CARD_COMPLETED,
                'targeted_employee_id' => factory(Employee::class),
                'loggable_id' => $task->id,
                'loggable_type' => 'Xguard\LaravelKanban\Models\Task'
            ]);
        }

        $data = app(GetAverageTimeToCompletionByBadgeAction::class)->fill(['start' => $start->format('Y-m-d H:i:s'), 'end' => $end->format('Y-m-d H:i:s'), ])->run();
        $this->assertCount(1, $data['names']);
        $this->assertCount(1, $data['hits']);
        $this->assertEquals(5, $data['hits'][0]); // This return the average number of hours for the one badge created which is (8+2)/2 = 5
    }
}
