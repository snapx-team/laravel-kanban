<?php

namespace Tests\Unit;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Xguard\LaravelKanban\Enums\SessionVariables;
use Xguard\LaravelKanban\Actions\Comments\CreateTaskCommentAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Comment;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Task;

class CreateTaskCommentActionTest extends TestCase
{

    use WithFaker, RefreshDatabase;

    const PLACEHOLDER_COMMENT = 'comment test';

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        Auth::setUser($user);

        $employee = factory(Employee::class)->states(Roles::ADMIN()->getValue())->create([Employee::USER_ID => $user->id,]);
        $board = factory(Board::class)->create();
        factory(Task::class)->create([Task::ID=>1, Task::REPORTER_ID => $employee->id, Task::BOARD_ID => $board->id]);
        factory(Member::class)->create([Member::EMPLOYEE_ID => $employee->id, Member::BOARD_ID => $board->id]);
        factory(Employee::class, 3)->create();

        session([SessionVariables::ROLE()->getValue() => Roles::ADMIN()->getValue(), SessionVariables::EMPLOYEE_ID()->getValue() => $employee->id]);
    }

    public function testAUserCanWriteCommentOnTaskIfTheyHaveBoardAccess()
    {
        $employee1 = factory(Employee::class)->create();
        $employee2 = factory(Employee::class)->create();
        $commentData = array(
            Comment::TASK_ID => 1,
            Comment::COMMENT => self::PLACEHOLDER_COMMENT,
            CreateTaskCommentAction::MENTIONS => array(
                $employee1->id,
                $employee2->id,
            )
        );

        app(CreateTaskCommentAction::class)->fill([CreateTaskCommentAction::COMMENT_DATA => $commentData])->run();

        $this->assertCount(1, Comment::all());
    }

    public function testCommentCreatesLog()
    {
        $commentData = array(
            Comment::TASK_ID => 1,
            Comment::COMMENT => self::PLACEHOLDER_COMMENT,
            CreateTaskCommentAction::MENTIONS => array()
        );

        app(CreateTaskCommentAction::class)->fill([CreateTaskCommentAction::COMMENT_DATA => $commentData])->run();

        $this->assertCount(1, Log::all());
    }

    public function testCommentWithMentionsCreatesLogs()
    {
        $employee1 = factory(Employee::class)->create();
        $employee2 = factory(Employee::class)->create();
        $commentData = array(
            Comment::TASK_ID => 1,
            Comment::COMMENT => self::PLACEHOLDER_COMMENT,
            CreateTaskCommentAction::MENTIONS => array(
                $employee1->id,
                $employee2->id,
            )
        );

        app(CreateTaskCommentAction::class)->fill([CreateTaskCommentAction::COMMENT_DATA => $commentData])->run();

        $this->assertCount(3, Log::all()); //two mentions log and one comment log
    }

    public function testMentionsCreatesNotifications()
    {
        $employee1 = factory(Employee::class)->create();
        $employee2 = factory(Employee::class)->create();
        $commentData = array(
            Comment::TASK_ID => 1,
            Comment::COMMENT => self::PLACEHOLDER_COMMENT,
            CreateTaskCommentAction::MENTIONS => array(
                $employee1->id,
                $employee2->id,
            )
        );

        app(CreateTaskCommentAction::class)->fill([CreateTaskCommentAction::COMMENT_DATA => $commentData])->run();

        $this->assertCount(2, DB::table('kanban_employee_log')->get());
    }
}
