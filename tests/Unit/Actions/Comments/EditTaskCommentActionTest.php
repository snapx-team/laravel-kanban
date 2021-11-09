<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Comments\EditTaskCommentAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Comment;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Task;

class EditTaskCommentActionTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        Auth::setUser($user);

        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $user->id,]);
        $board = factory(Board::class)->create();
        $task = factory(Task::class)->create(['reporter_id' => $employee->id, 'board_id' => $board->id]);
        factory(Member::class)->create(['employee_id' => $employee->id, 'board_id' => $board->id]);
        factory(Comment::class)->create(['comment' => 'test comment', 'task_id'=> $task->id, 'employee_id' => $employee->id]);
        factory(Employee::class, 3)->create();

        session(['role' => 'admin', 'employee_id' => $employee->id]);
    }

    public function testAUserCanEditCommentTheyCreated()
    {

        $this->assertDatabaseHas('kanban_comments', [
            'comment' => 'test comment',
        ]);

        $commentData = array(
            'id' => 1,
            'comment' => 'updated comment',
            'employee_id' => session('employee_id'),
            'mentions' => array()
        );

        app(EditTaskCommentAction::class)->fill(['comment_data' => $commentData])->run();

        $this->assertDatabaseMissing('kanban_comments', [
            'comment' => 'test comment',
        ]);
        $this->assertDatabaseHas('kanban_comments', [
            'comment' => 'updated comment',
        ]);
    }

    public function testAUserCannotEditCommentTheyDidntCreated()
    {
        $this->expectException(AuthorizationException::class);
        $this->assertDatabaseHas('kanban_comments', [
            'comment' => 'test comment',
        ]);

        $commentData = array(
            'id' => 1,
            'comment' => 'updated comment',
            'employee_id' => 2,
            'mentions' => array()
        );

        app(EditTaskCommentAction::class)->fill(['comment_data' => $commentData])->run();

        $this->assertDatabaseHas('kanban_comments', [
            'comment' => 'test comment',
        ]);
    }
}
