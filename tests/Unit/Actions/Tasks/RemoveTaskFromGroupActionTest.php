<?php

namespace Tests\Unit\Actions\Tasks;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Tasks\RemoveTaskFromGroupAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Models\Task;

class RemoveTaskFromGroupActionTest extends TestCase
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

    public function testRemoveTaskFromGroupActionUpdatesSharedTaskDataCreatesLogsAndVersion()
    {
        $sharedTaskData = factory(SharedTaskData::class)->create();
        $task = factory(Task::class)->create(['shared_task_data_id' => $sharedTaskData->id]);

        app(RemoveTaskFromGroupAction::class)->fill(['taskId' => $task->id])->run();
        $task->refresh();

        $this->assertNotEquals($sharedTaskData->id, $task->shared_task_data_id);
        $this->assertEquals($sharedTaskData->description, $sharedTaskData->description);

        $payloadLog = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_CARD_REMOVED_FROM_GROUP,
            'description' => 'Task [' . $task->task_simple_name . '] was removed from group',
            'targeted_employee_id' => null,
            'loggable_id' => $task->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task'
        ];

        $log = Log::first();

        $payloadTaskVersion = [
            "index" => $task->index,
            "name" => $task->name,
            "deadline" => date('y-m-d h:m', strtotime($task->deadline)),
            "shared_task_data_id" =>$task->shared_task_data_id,
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
    }

    public function testRemoveTaskFromGroupActionThrowsErrorOnInvalidId()
    {
        $this->expectException(Exception::class);
        app(RemoveTaskFromGroupAction::class)->fill(['taskId' => 1])->run();
    }
}


