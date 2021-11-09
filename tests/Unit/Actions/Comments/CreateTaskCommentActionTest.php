<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Action;
use Tests\TestCase;
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

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        Auth::setUser($user);

        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $user->id,]);
        $board = factory(Board::class)->create();
        factory(Task::class)->create(['id'=>1, 'reporter_id' => $employee->id, 'board_id' => $board->id]);
        factory(Member::class)->create(['employee_id' => $employee->id, 'board_id' => $board->id]);
        factory(Employee::class, 3)->create();

        session(['role' => 'admin', 'employee_id' => $employee->id]);
    }

    public function testAUserCanWriteCommentOnTaskIfTheyHaveBoardAccess()
    {

        $commentData = array(
            'task_id' => 1,
            'comment' => 'comment test',
            'mentions' => array(
                '0' => 1,
                '1' => 2,
                '2' => 3
            )
        );

        app(CreateTaskCommentAction::class)->fill(['comment_data' => $commentData])->run();

        $this->assertCount(1, Comment::all());
    }

    public function testCommentCreatesLog()
    {
        $commentData = array(
            'task_id' => 1,
            'comment' => 'comment test',
            'mentions' => array()
        );

        app(CreateTaskCommentAction::class)->fill(['comment_data' => $commentData])->run();

        $this->assertCount(1, Log::all());
    }

    public function testCommentWithMentionsCreatesLogs()
    {
        $commentData = array(
            'task_id' => 1,
            'comment' => 'comment test',
            'mentions' => array(
                '0' => 1,
                '1' => 2,
                '2' => 3
            )
        );

        app(CreateTaskCommentAction::class)->fill(['comment_data' => $commentData])->run();

        $this->assertCount(4, Log::all());
    }

    public function testMentionsCreatesNotifications()
    {
        $commentData = array(
            'task_id' => 1,
            'comment' => 'comment test',
            'mentions' => array(
                '0' => 1,
                '1' => 2,
                '2' => 3
            )
        );

        app(CreateTaskCommentAction::class)->fill(['comment_data' => $commentData])->run();

        $this->assertCount(3, DB::table('kanban_employee_log')->get());
    }
}
