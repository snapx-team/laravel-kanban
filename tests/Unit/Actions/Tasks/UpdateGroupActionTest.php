<?php

namespace Tests\Unit\Actions\Tasks;

use App\Models\User;
use Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Tasks\UpdateGroupAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Models\Task;

class UpdateGroupActionTest extends TestCase
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
        $sharedTaskDatas = factory(SharedTaskData::class, 2)->create();
        $tasks = factory(Task::class, 2)->create(['shared_task_data_id' => $sharedTaskDatas[0]->id]);

        app(UpdateGroupAction::class)->fill([
            'taskId' => $tasks[0]->id,
            'groupId' => $sharedTaskDatas[1]->id
        ])->run();

        $tasks[0]->refresh();

        $payloadLog = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_CARD_ASSIGNED_GROUP,
            'description' => 'Task [' . $tasks[0]->task_simple_name . '] changed group',
            'targeted_employee_id' => null,
            'loggable_id' => $tasks[0]->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task'
        ];

        $log = Log::first();

        $payloadTaskVersion = [
            "index" => $tasks[0]->index,
            "name" => $tasks[0]->name,
            "deadline" => Carbon::parse($tasks[0]->deadline),
            "shared_task_data_id" => $tasks[0]->shared_task_data_id,
            "reporter_id" => $tasks[0]->reporter_id,
            "column_id" => $tasks[0]->column_id,
            "row_id" => $tasks[0]->row_id,
            "board_id" => $tasks[0]->board_id,
            "badge_id" => $tasks[0]->badge_id,
            "status" => $tasks[0]->status,
            "task_id" => $tasks[0]->id,
            "log_id" => $log->id
        ];

        $this->assertDatabaseHas('kanban_logs', $payloadLog);
        $this->assertDatabaseHas('kanban_tasks_versions', $payloadTaskVersion);
        $this->assertEquals(2, SharedTaskData::count());
        $this->assertNotEquals($sharedTaskDatas[0]->id, $tasks[0]->shared_task_data_id);
    }

    public function testUpdateGroupActionThrowsErrorOnInvalidId()
    {
        $this->expectException(Exception::class);
        $sharedTaskData = factory(SharedTaskData::class)->create();

        app(UpdateGroupAction::class)->fill([
            'taskId' => 1,
            'groupId' => $sharedTaskData->id
        ])->run();
    }

    public function testUpdateGroupActionThrowsValidationErrorOnNullTaskId()
    {
        $this->expectException(ValidationException::class);
        $sharedTaskData = factory(SharedTaskData::class)->create();

        app(UpdateGroupAction::class)->fill([
            'taskId' => null,
            'groupId' => $sharedTaskData->id
        ])->run();
    }

    public function testUpdateGroupActionThrowsValidationErrorOnNullSharedTaskId()
    {
        $this->expectException(ValidationException::class);
        $task = factory(Task::class)->create();

        app(UpdateGroupAction::class)->fill([
            'taskId' => $task->id,
            'groupId' => null
        ])->run();
    }

    public function testUpdateGroupActionDeletesGroupIfNoTaskPointToIt()
    {
        $sharedTaskDatas = factory(SharedTaskData::class, 2)->create();
        $task = factory(Task::class)->create(['shared_task_data_id' => $sharedTaskDatas[0]->id]);

        app(UpdateGroupAction::class)->fill([
            'taskId' => $task->id,
            'groupId' => $sharedTaskDatas[1]->id
        ])->run();

        $task->refresh();

        $payloadLog = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_CARD_ASSIGNED_GROUP,
            'description' => 'Task [' . $task->task_simple_name . '] changed group',
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
        $this->assertEquals(1, SharedTaskData::count());
        $this->assertNotEquals($sharedTaskDatas[0]->id, $task->shared_task_data_id);
    }
}


