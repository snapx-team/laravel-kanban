<?php

namespace Tests\Unit\Actions\Tasks;

use App\Models\User;
use Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Tasks\UpdateTaskStatusAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

class UpdateTaskStatusActionTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $user = factory(User::class)->create();
        Auth::setUser($user);
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $user->id]);
        session(['role' => 'admin', 'employee_id' => $employee->id]);
    }

    public function testUpdateTaskStatusActionUpdatesStatusToActiveCreatesLogsAndVersion()
    {
        $task = factory(Task::class)->create(['status'=> 'test', 'index' => 1]);

        app(UpdateTaskStatusAction::class)->fill([
            'taskId' => $task->id,
            'newStatus' => 'active'
        ])->run();

        $task->refresh();

        $payloadLog = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_CARD_ACTIVATED,
            'description' => 'Task [' . $task->task_simple_name . '] in board [' . $task->board->name . '] set to active',
            'targeted_employee_id' => null,
            'loggable_id' => $task->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task'
        ];

        $log = Log::first();

        $payloadTaskVersion = [
            "index" => $task->index,
            "name" => $task->name,
            "deadline" => Carbon::parse($task->deadline),
            "shared_task_data_id" => $task->shared_task_data_id,
            "reporter_id" => $task->reporter_id,
            "column_id" => $task->column_id,
            "row_id" => $task->row_id,
            "board_id" => $task->board_id,
            "badge_id" => $task->badge_id,
            "status" => $task->status,
            "task_id" => $task->id,
            "log_id" => $log->id
        ];

        $this->assertDatabaseHas('kanban_logs', $payloadLog);
        $this->assertDatabaseHas('kanban_tasks_versions', $payloadTaskVersion);
        $this->assertEquals('active', $task->status);
        $this->assertNotNull($task->row_id);
        $this->assertNotNull($task->column_id);
        $this->assertNotNull($task->index);
    }

    public function testUpdateTaskStatusActionUpdatesStatusToCancelledCreatesLogsAndVersion()
    {
        $task = factory(Task::class)->create(['status'=> 'test', 'index' => 1]);

        app(UpdateTaskStatusAction::class)->fill([
            'taskId' => $task->id,
            'newStatus' => 'cancelled'
        ])->run();

        $task->refresh();

        $payloadLog = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_CARD_CANCELLED,
            'description' => 'Task [' . $task->task_simple_name . '] in board [' . $task->board->name . '] set to cancelled',
            'targeted_employee_id' => null,
            'loggable_id' => $task->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task'
        ];

        $log = Log::first();

        $payloadTaskVersion = [
            "index" => $task->index,
            "name" => $task->name,
            "deadline" => Carbon::parse($task->deadline),
            "shared_task_data_id" => $task->shared_task_data_id,
            "reporter_id" => $task->reporter_id,
            "column_id" => $task->column_id,
            "row_id" => $task->row_id,
            "board_id" => $task->board_id,
            "badge_id" => $task->badge_id,
            "status" => $task->status,
            "task_id" => $task->id,
            "log_id" => $log->id
        ];

        $this->assertDatabaseHas('kanban_logs', $payloadLog);
        $this->assertDatabaseHas('kanban_tasks_versions', $payloadTaskVersion);
        $this->assertEquals('cancelled', $task->status);
        $this->assertNull($task->row_id);
        $this->assertNull($task->column_id);
        $this->assertNull($task->index);
    }

    public function testUpdateTaskStatusActionUpdatesStatusToCompletedCreatesLogsAndVersion()
    {
        $task = factory(Task::class)->create(['status'=> 'test', 'index' => 1]);

        app(UpdateTaskStatusAction::class)->fill([
            'taskId' => $task->id,
            'newStatus' => 'completed'
        ])->run();

        $task->refresh();

        $payloadLog = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_CARD_COMPLETED,
            'description' => 'Task [' . $task->task_simple_name . '] in board [' . $task->board->name . '] set to completed',
            'targeted_employee_id' => null,
            'loggable_id' => $task->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task'
        ];

        $log = Log::first();

        $payloadTaskVersion = [
            "index" => $task->index,
            "name" => $task->name,
            "deadline" => Carbon::parse($task->deadline),
            "shared_task_data_id" => $task->shared_task_data_id,
            "reporter_id" => $task->reporter_id,
            "column_id" => $task->column_id,
            "row_id" => $task->row_id,
            "board_id" => $task->board_id,
            "badge_id" => $task->badge_id,
            "status" => $task->status,
            "task_id" => $task->id,
            "log_id" => $log->id
        ];

        $this->assertDatabaseHas('kanban_logs', $payloadLog);
        $this->assertDatabaseHas('kanban_tasks_versions', $payloadTaskVersion);
        $this->assertEquals('completed', $task->status);
        $this->assertNull($task->row_id);
        $this->assertNull($task->column_id);
        $this->assertNull($task->index);
    }

    public function testUpdateTaskStatusActionThrowsValidationExceptionOnInvalidNewStatus()
    {
        $this->expectException(Exception::class);

        $task = factory(Task::class)->create();

        app(UpdateTaskStatusAction::class)->fill([
            'taskId' => $task->id,
            'newStatus' => 'test'
        ])->run();
    }

    public function testUpdateTaskStatusActionActionThrowsErrorOnInvalidTaskId()
    {
        $this->expectException(Exception::class);

        app(UpdateTaskStatusAction::class)->fill([
            'taskId' => 1,
            'newStatus' => 'test'
        ])->run();
    }

    public function testUpdateTaskStatusActionThrowsValidationErrorOnNullnewStatus()
    {
        $this->expectException(ValidationException::class);

        app(UpdateTaskStatusAction::class)->fill([
            'taskId' => 1,
            'newStatus' => null
        ])->run();
    }

    public function testUpdateTaskStatusActionThrowsValidationErrorOnNullTaskId()
    {
        $this->expectException(ValidationException::class);

        app(UpdateTaskStatusAction::class)->fill([
            'taskId' => null,
            'newStatus' => 'test'
        ])->run();
    }
}

