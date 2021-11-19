<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Comments\DeleteTaskCommentAction;
use Xguard\LaravelKanban\Actions\Comments\EditTaskCommentAction;
use Xguard\LaravelKanban\Models\Comment;
use Xguard\LaravelKanban\Models\Employee;

class DeleteTaskCommentActionTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $user->id,]);
        Auth::setUser($user);
        session(['role' => 'admin', 'employee_id' => $employee->id]);
    }

    public function testAUserCanEditCommentTheyCreated()
    {

        $comment = factory(Comment::class)->create([
            'comment' => 'test comment',
            'employee_id' => session('employee_id')
        ]);

        $this->assertDatabaseHas('kanban_comments', [
            'comment' => 'test comment',
        ]);

        app(DeleteTaskCommentAction::class)->fill(['comment_data' => $comment])->run();

        $this->assertSoftDeleted('kanban_comments', [
            'comment' => 'test comment',
        ]);
    }

    public function testAUserCannotDeleteCommentTheyDidntCreated()
    {
        $this->expectException(AuthorizationException::class);
        $comment = factory(Comment::class)->create([
                'comment' => 'test comment',
                'employee_id' => 2
            ]);

        $this->assertDatabaseHas('kanban_comments', [
            'comment' => 'test comment',
        ]);

        app(DeleteTaskCommentAction::class)->fill(['comment_data' => $comment])->run();

        $this->assertDatabaseMissing('kanban_comments', [
            'comment' => 'test comment',
            'deleted_at' => null
        ]);
    }
}
