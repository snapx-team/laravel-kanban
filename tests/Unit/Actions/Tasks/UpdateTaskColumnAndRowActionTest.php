<?php

namespace Tests\Unit\Actions\Tasks;

use App\Models\User;
use Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Tasks\UpdateTaskColumnAndRowAction;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Row;
use Xguard\LaravelKanban\Models\Task;

class UpdateTaskColumnAndRowActionTest extends TestCase
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
    }

    public function testUpdateGroupActionUpdatesSharedTaskDataCreatesLogsAndVersion()
    {
        $task = factory(Task::class)->create();
        $oldColumn = Column::find($task->column_id);
        $oldRow = Row::find($task->row_id);
        $newColumn = factory(Column::class)->create();
        $newRow = factory(Row::class)->create();

        app(UpdateTaskColumnAndRowAction::class)->fill([
            'columnId' => $newColumn->id,
            'rowId' => $newRow->id,
            'taskId' => $task->id,
        ])->run();

        $task->refresh();

        $payloadLog = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_CARD_MOVED,
            'description' => 'Task [' . $task->task_simple_name . '] moved from [' . $oldRow->name . ':' .  $oldColumn->name . '] to [' . $task->row->name . ':' . $task->column->name . ']',
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
        $this->assertNotEquals($oldColumn->id, $newColumn->id);
        $this->assertNotEquals($oldRow->id, $newRow->id);
    }

    public function testUpdateGroupActionThrowsErrorOnInvalidTaskId()
    {
        $this->expectException(Exception::class);

        app(UpdateTaskColumnAndRowAction::class)->fill([
            'columnId' => 1,
            'rowId' => 1,
            'taskId' => 1,
        ])->run();
    }

    public function testUpdateTaskColumnAndRowActionThrowsValidationErrorOnNullColumnId()
    {
        $this->expectException(ValidationException::class);

        app(UpdateTaskColumnAndRowAction::class)->fill([
            'columnId' => null,
            'rowId' => 1,
            'taskId' => 1,
        ])->run();
    }

    public function testUpdateTaskColumnAndRowActionThrowsValidationErrorOnNullRowId()
    {
        $this->expectException(ValidationException::class);

        app(UpdateTaskColumnAndRowAction::class)->fill([
            'columnId' => 1,
            'rowId' => null,
            'taskId' => 1,
        ])->run();
    }

    public function testUpdateTaskColumnAndRowActionThrowsValidationErrorOnNullTaskId()
    {
        $this->expectException(ValidationException::class);

        app(UpdateTaskColumnAndRowAction::class)->fill([
            'columnId' => 1,
            'rowId' => 1,
            'taskId' => null,
        ])->run();
    }
}


