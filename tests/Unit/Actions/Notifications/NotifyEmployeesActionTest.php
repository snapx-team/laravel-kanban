<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Notifications\NotifyEmployeesAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\EmployeeBoardNotificationSetting;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\Comment;

class NotifyEmployeesActionTest extends TestCase
{

    use WithFaker, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $user = factory(User::class)->create();
        Auth::setUser($user);
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $user->id,]);
        session(['role' => 'admin', 'employee_id' => $employee->id]);
    }

    public function testANotificationIsCreatedWhenLogWithNotifiableEmployeeIsCreated()
    {
        $task = factory(Task::class)->create(['reporter_id' => session('employee_id')]);
        $comment = factory(Comment::class)->create([ 'task_id' => $task->id, 'employee_id' => session('employee_id')]);

        $log = Log::create([
            'user_id' => session('employee_id'),
            'log_type' => Log::TYPE_COMMENT_CREATED,
            'description' => 'test',
            'targeted_employee_id' => session('employee_id'),
            'loggable_id' => $comment->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Comment'
        ]);

        (new NotifyEmployeesAction(['log' => $log]))->run();
        $this->assertCount(1, DB::table('kanban_employee_log')->get());
    }

    public function testANotificationIsNotCreatedIfEmployeeBoardNotificationSettingsIsSetToIgnoreTheLogGroup()
    {
        $board = factory(Board::class)->create();
        $task = factory(Task::class)->create(['reporter_id' => session('employee_id'), 'board_id' => $board->id]);
        $comment = factory(Comment::class)->create([ 'task_id' => $task->id, 'employee_id' => session('employee_id')]);

        $arrayOfLogGroupsToIgnore = array('TASK_STATUS_GROUP', 'TASK_MOVEMENT_GROUP', 'COMMENT_GROUP');
        $serializedArrayOfLogGroupsToIgnore = serialize($arrayOfLogGroupsToIgnore);
        EmployeeBoardNotificationSetting::create(['board_id' => $board->id, 'employee_id' => session('employee_id'), 'ignore_types' => $serializedArrayOfLogGroupsToIgnore]);

        $log = Log::create([
            'user_id' => session('employee_id'),
            'log_type' => Log::TYPE_COMMENT_CREATED,
            'description' => 'test',
            'targeted_employee_id' => session('employee_id'),
            'loggable_id' => $comment->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Comment'
        ]);

        (new NotifyEmployeesAction(['log' => $log]))->run();
        $this->assertCount(0, DB::table('kanban_employee_log')->get());
    }
}
