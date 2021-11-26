<?php

namespace Tests\Unit;

use App\Models\User;
use DateTime;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Metrics\GetClosedTasksByAssignedToAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

class GetClosedTasksByAssignedToActionTest extends TestCase
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

    public function testGetClosedTasksByAssignedToEmployeeBetweenSelectedDates()
    {
        $employee = factory(Employee::class)->create();
        $task1 = factory(Task::class)->create(['status' => 'completed']);
        $task2 = factory(Task::class)->create(['status' => 'completed']);

        $end = new DateTime('now');
        $start = new DateTime('yesterday');
        $twoDaysAgo = new DateTime('2 days ago');

        factory(Log::class)->create([
            'log_type' => LOG::TYPE_CARD_COMPLETED,
            'loggable_id' => $task1->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task',
        ]);

        factory(Log::class)->create([
            'log_type' => LOG::TYPE_CARD_COMPLETED,
            'loggable_id' => $task2->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task',
            'created_at' => $twoDaysAgo->format('Y-m-d H:i:s')
        ]);

        DB::table('kanban_employee_task')->insert([
            'employee_id' => $employee->id,
            'task_id' => $task1->id,
        ]);

        DB::table('kanban_employee_task')->insert([
            'employee_id' => $employee->id,
            'task_id' => $task2->id,
        ]);

        $data = app(GetClosedTasksByAssignedToAction::class)->fill(['start' => $start->format('Y-m-d H:i:s'), 'end' => $end->format('Y-m-d H:i:s'),])->run();
        $this->assertCount(1, $data['names']); //assert only 1 because we only want tasks closed by admins
        $this->assertCount(1, $data['hits']); //assert only 1 because we only want tasks closed by admins
        $this->assertEquals(1, $data['hits'][0]);
    }
}
