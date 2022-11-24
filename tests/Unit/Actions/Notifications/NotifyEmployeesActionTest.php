<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Enums\SessionVariables;
use Xguard\LaravelKanban\Actions\Notifications\NotifyEmployeesAction;
use Xguard\LaravelKanban\Enums\Roles;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\EmployeeBoardNotificationSetting;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\Comment;

class NotifyEmployeesActionTest extends TestCase
{

    use WithFaker, RefreshDatabase;

    const KANBAN_EMPLOYEE_LOG_TABLE_NAME = 'kanban_employee_log';
    const LOG = 'log';
    const EMPTY_STRING = '';

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $user = factory(User::class)->create();
        Auth::setUser($user);
        $employee = factory(Employee::class)->states(Roles::ADMIN()->getValue())->create([Employee::USER_ID => $user->id,]);
        session([
            SessionVariables::ROLE()->getValue() => Roles::ADMIN()->getValue(),
            SessionVariables::EMPLOYEE_ID()->getValue() => $employee->id
        ]);
    }

    public function testANotificationIsCreatedWhenLogWithNotifiableEmployeeIsCreated()
    {
        $task = factory(Task::class)->create([Task::REPORTER_ID => session(SessionVariables::EMPLOYEE_ID()->getValue())]);
        $comment = factory(Comment::class)->create([
            Comment::TASK_ID => $task->id, Comment::EMPLOYEE_ID => session(SessionVariables::EMPLOYEE_ID()->getValue())
        ]);
        $employee = factory(Employee::class)->states(Roles::ADMIN()->getValue())->create();

        $log = Log::create([
            Log::USER_ID => session(SessionVariables::EMPLOYEE_ID()->getValue()),
            Log::LOG_TYPE => Log::TYPE_COMMENT_CREATED,
            Log::DESCRIPTION => self::EMPTY_STRING,
            Log::TARGETED_EMPLOYEE_ID => $employee->id,
            Log::LOGGABLE_ID => $comment->id,
            Log::LOGGABLE_TYPE => LoggableTypes::COMMENT()->getValue()
        ]);

        (new NotifyEmployeesAction([self::LOG => $log]))->run();
        $this->assertCount(1, DB::table(self::KANBAN_EMPLOYEE_LOG_TABLE_NAME)->get());
    }

    public function testANotificationIsNotCreatedIfEmployeeBoardNotificationSettingsIsSetToIgnoreTheLogGroup()
    {
        $board = factory(Board::class)->create();
        $task = factory(Task::class)->create([
            Task::REPORTER_ID => session(SessionVariables::EMPLOYEE_ID()->getValue()), Task::BOARD_ID => $board->id
        ]);
        $comment = factory(Comment::class)->create([
            Comment::TASK_ID => $task->id, Comment::EMPLOYEE_ID => session(SessionVariables::EMPLOYEE_ID()->getValue())
        ]);
        $employee = factory(Employee::class)->states(Roles::ADMIN()->getValue())->create();

        $arrayOfLogGroupsToIgnore = array(Log::TASK_STATUS_GROUP, Log::TASK_MOVEMENT_GROUP, Log::COMMENT_GROUP);
        $serializedArrayOfLogGroupsToIgnore = serialize($arrayOfLogGroupsToIgnore);
        EmployeeBoardNotificationSetting::create([
            EmployeeBoardNotificationSetting::BOARD_ID => $board->id, EmployeeBoardNotificationSetting::EMPLOYEE_ID => $employee->id,
            EmployeeBoardNotificationSetting::IGNORE_TYPES => $serializedArrayOfLogGroupsToIgnore
        ]);

        $log = Log::create([
            Log::USER_ID => session(SessionVariables::EMPLOYEE_ID()->getValue()),
            Log::LOG_TYPE => Log::TYPE_COMMENT_CREATED,
            Log::DESCRIPTION => self::EMPTY_STRING,
            Log::TARGETED_EMPLOYEE_ID => $employee->id,
            Log::LOGGABLE_ID => $comment->id,
            Log::LOGGABLE_TYPE => LoggableTypes::COMMENT()->getValue()
        ]);

        (new NotifyEmployeesAction([self::LOG => $log]))->run();
        $this->assertCount(0, DB::table(self::KANBAN_EMPLOYEE_LOG_TABLE_NAME)->get());
    }

    public function testANotificationIsNotCreatedIfEmployeePerformedTheNotifiableAction()
    {
        $board = factory(Board::class)->create();
        $task = factory(Task::class)->create([
            Task::REPORTER_ID => session(SessionVariables::EMPLOYEE_ID()->getValue()), Task::BOARD_ID => $board->id
        ]);
        $comment = factory(Comment::class)->create([
            Comment::TASK_ID => $task->id, Comment::EMPLOYEE_ID => session(SessionVariables::EMPLOYEE_ID()->getValue())
        ]);

        $log = Log::create([
            Log::USER_ID => session(SessionVariables::EMPLOYEE_ID()->getValue()),
            Log::LOG_TYPE => Log::TYPE_COMMENT_CREATED,
            Log::DESCRIPTION => self::EMPTY_STRING,
            Log::TARGETED_EMPLOYEE_ID => session(SessionVariables::EMPLOYEE_ID()->getValue()),
            Log::LOGGABLE_ID => $comment->id,
            Log::LOGGABLE_TYPE => LoggableTypes::COMMENT()->getValue()
        ]);

        (new NotifyEmployeesAction([self::LOG => $log]))->run();
        $this->assertCount(0, DB::table(self::KANBAN_EMPLOYEE_LOG_TABLE_NAME)->get());
    }
}
