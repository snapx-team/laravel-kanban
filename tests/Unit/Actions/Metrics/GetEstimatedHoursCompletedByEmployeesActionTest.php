<?php

namespace Tests\Unit;

use App\Models\User;
use DateTime;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Metrics\GetEstimatedHoursCompletedByEmployeesAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

class GetEstimatedHoursCompletedByEmployeesActionTest extends TestCase
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

    public function testGetEstimatedHourCompletedIsAccurate()
    {
        $employee = factory(Employee::class)->create();
        $task1 = factory(Task::class)->create(['status' => 'completed', 'time_estimate' => 8]);
        $task2 = factory(Task::class)->create(['status' => 'completed', 'time_estimate' => 2]);

        $end = new DateTime('now');
        $start = new DateTime('yesterday');

        $tasks = [$task1, $task2];

        foreach ($tasks as $task) {
            factory(Log::class)->create([
                'log_type' => LOG::TYPE_CARD_COMPLETED,
                'loggable_id' => $task->id,
                'loggable_type' => 'Xguard\LaravelKanban\Models\Task',
            ]);
            DB::table('kanban_employee_task')->insert([
                'employee_id' => $employee->id,
                'task_id' => $task->id,
            ]);
        }

        $data = app(GetEstimatedHoursCompletedByEmployeesAction::class)->fill(['start' => $start->format('Y-m-d H:i:s'), 'end' => $end->format('Y-m-d H:i:s'),])->run();
        $this->assertCount(1, $data['names']);
        $this->assertCount(1, $data['hits']);
        $this->assertEquals(10, $data['hits'][0]);  //  This return the total number of hours for the one badge created which is 8+2 = 10
    }
}
