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
        $this->seed();
        $user = factory(User::class)->create();
        Auth::setUser($user);

        Board::create(['name' => 'test board']);
        Employee::create(['user_id' => $user->id, 'role' => 'admin']);
        Employee::create(['user_id' => '1', 'role' => 'admin']);
        Employee::create(['user_id' => '2', 'role' => 'admin']);
        Employee::create(['user_id' => '3', 'role' => 'admin']);
        Member::create(['employee_id' => '1', 'board_id' => 1]);
        Task::create(['name' => 'test task', 'board_id'=> 1, 'reporter_id' => 1]);
        session(['role' => 'admin', 'employee_id' => '1']);
    }

    public function testAUserCanWriteCommentOnTaskIfTheyHaveBoardAccess()
    {

        $commentData = array(
            'taskId' => 1,
            'comment' => 'comment test',
            'mentions' => array(
                '0' => 1,
                '1' => 2,
                '2' => 3
            )
        );

        (new CreateTaskCommentAction(['comment_data' => $commentData]))->run();

        $this->assertCount(1, Comment::all());
    }

    public function testCommentCreatesLog()
    {
        $commentData = array(
            'taskId' => 1,
            'comment' => 'comment test',
            'mentions' => array()
        );

        (new CreateTaskCommentAction(['comment_data' => $commentData]))->run();

        $this->assertCount(1, Log::all());
    }

    public function testCommentWithMentionsCreatesLogs()
    {
        $commentData = array(
            'taskId' => 1,
            'comment' => 'comment test',
            'mentions' => array(
                '0' => 1,
                '1' => 2,
                '2' => 3
            )
        );

        (new CreateTaskCommentAction(['comment_data' => $commentData]))->run();

        $this->assertCount(4, Log::all());
    }

    public function testMentionsCreatesNotifications()
    {
        $commentData = array(
            'taskId' => 1,
            'comment' => 'comment test',
            'mentions' => array(
                '0' => 1,
                '1' => 2,
                '2' => 3
            )
        );

        (new CreateTaskCommentAction(['comment_data' => $commentData]))->run();

        $this->assertCount(3, DB::table('kanban_employee_log')->get());
    }
}
