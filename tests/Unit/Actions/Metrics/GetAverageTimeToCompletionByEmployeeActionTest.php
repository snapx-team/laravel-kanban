<?php

namespace Tests\Unit;

use App\Models\User;
use DateTime;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Metrics\GetAverageTimeToCompletionByEmployeeAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

class GetAverageTimeToCompletionByEmployeeActionTest extends TestCase
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

    public function testAverageTimeToCompletionByEmployeeRetrievesAccurateData()
    {
        $employee = factory(Employee::class)->create();

        $task1 = factory(Task::class)->create();


        $task2 = factory(Task::class)->create();
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
            'targeted_employee_id' => $employee->id,
            'loggable_id' => $task2->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task',
            'created_at' => $eightHours->format('Y-m-d H:i:s')
        ]);

        foreach ($tasks as $task) {
            DB::table('kanban_employee_task')->insert([
                'employee_id' => session('employee_id'),
                'task_id' => $task->id,
            ]);

            factory(Log::class)->create([
                'user_id' => factory(User::class),
                'log_type' => LOG::TYPE_CARD_COMPLETED,
                'targeted_employee_id' => factory(Employee::class),
                'loggable_id' => $task->id,
                'loggable_type' => 'Xguard\LaravelKanban\Models\Task'
            ]);
        }

        $data = app(GetAverageTimeToCompletionByEmployeeAction::class)->fill(['start' => $start->format('Y-m-d H:i:s'), 'end' => $end->format('Y-m-d H:i:s'),])->run();
        $this->assertCount(1, $data['names']);
        $this->assertCount(1, $data['hits']);
        $this->assertEquals(5, $data['hits'][0]); // This return the average number of hours for employee to complete task (8+2)/2 = 5
    }
}
