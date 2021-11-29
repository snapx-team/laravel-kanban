<?php

namespace Tests\Unit\Actions\Tasks;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Tasks\SyncAssignedEmployeesToTaskAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

class SyncAssignedEmployeesToTaskActionTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $user = factory(User::class)->create();
        Auth::setUser($user);
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $user->id,]);
        session(['role' => 'admin', 'employee_id' => $employee->id]);
        $this->task = factory(Task::class)->create();
    }

    public function testSyncAssignedEmployeesToTaskActionAssignsNewUserToTaskAndCreatesLog()
    {
        $assignedEmployee = factory(Employee::class, 2)->create();

        app(SyncAssignedEmployeesToTaskAction::class)->fill(['assignedTo' => [['employee' => ['id'=> $assignedEmployee[0]->id]]], 'task' => $this->task])->run();
        $this->task->refresh();

        $assignedEmployeesIds = $this->task->assignedTo()->pluck('employee_id')->toArray();
        $assignedEmployeesCount = count($assignedEmployeesIds);

        $this->assertTrue(in_array($assignedEmployee[0]->id, $assignedEmployeesIds));
        $this->assertEquals(1,  $assignedEmployeesCount);

        app(SyncAssignedEmployeesToTaskAction::class)->fill(['assignedTo' => [['employee' => ['id'=> $assignedEmployee[1]->id]]], 'task' => $this->task])->run();
        $this->task->refresh();

        $assignedEmployeesIds = $this->task->assignedTo()->pluck('employee_id')->toArray();
        $assignedEmployeesCount = count($assignedEmployeesIds);

        $this->assertTrue(in_array($assignedEmployee[1]->id, $assignedEmployeesIds));
        $this->assertEquals(1,  $assignedEmployeesCount);

        $payloadLog1 = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_CARD_ASSIGNED_TO_USER,
            'description' => 'User ' . $assignedEmployee[0]->user->full_name . ' has been assigned to task [' . $this->task->task_simple_name . ']',
            'targeted_employee_id' => $assignedEmployee[0]->id,
            'loggable_id' =>  $this->task->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task'
        ];
        $payloadLog2 = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_CARD_ASSIGNED_TO_USER,
            'description' => 'User ' . $assignedEmployee[1]->user->full_name . ' has been assigned to task [' . $this->task->task_simple_name . ']',
            'targeted_employee_id' => $assignedEmployee[1]->id,
            'loggable_id' =>  $this->task->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task'
        ];
        $payloadLog3 = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_CARD_UNASSIGNED_TO_USER,
            'description' => 'User ' . $assignedEmployee[0]->user->full_name . ' has been unassigned from task [' . $this->task->task_simple_name . ']',
            'targeted_employee_id' => $assignedEmployee[0]->id,
            'loggable_id' => $this->task->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task'
        ];

        $this->assertDatabaseHas('kanban_logs', $payloadLog1);
        $this->assertDatabaseHas('kanban_logs', $payloadLog2);
        $this->assertDatabaseHas('kanban_logs', $payloadLog3);
    }

    public function testSyncAssignedEmployeesToTaskActionThrowsValidationErrorGivenNull()
    {
        $this->expectException(ValidationException::class);
        app(SyncAssignedEmployeesToTaskAction::class)->fill(['task' => null])->run();
    }
}


