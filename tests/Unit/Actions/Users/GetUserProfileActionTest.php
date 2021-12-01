<?php

namespace Tests\Unit\Actions\Users;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Users\GetUserProfileAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Task;

class GetUserProfileActionTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        Auth::setUser($this->user);
        $this->employee = factory(Employee::class)->states('admin')->create(['user_id' => $this->user->id]);
        session(['role' => 'admin', 'employee_id' => $this->employee->id]);
    }

    public function testGetUserProfileAction()
    {
        $boardCount = rand (1, 10);
        $taskAssignedToCount = rand (5, 10);
        $taskCompletedCount = rand (0, 4);
        $taskCreatedCount = rand (1, 10);

        $boards = factory(Board::class, $boardCount)->create();
        
        foreach ($boards as $board) {
            factory(Member::class)->create([
                'board_id' => $board->id,
                'employee_id' => $this->employee->id,
            ]);
        }

        $tasksAssignedTo = factory(Task::class, $taskAssignedToCount)->create();
        
        factory(Task::class, $taskCreatedCount)->create(['reporter_id' => $this->employee->id]);

        for ($i = 0; $i < $taskCompletedCount; $i++) {
            $tasksAssignedTo[$i]['status'] = 'completed';
            $tasksAssignedTo[$i]->save();
        }

        foreach ($tasksAssignedTo as $task) {
            $this->employee->tasks()->attach($task);
        }

        $result = (new GetUserProfileAction)->run();

        $this->assertEquals($this->employee->user->full_name, $result['userName']);
        $this->assertEquals($this->employee->role, $result['userStatus']);
        $this->assertEquals(Carbon::parse($this->employee->created_at)->toDateString(), $result['userCreatedAt']);
        $this->assertEquals($boardCount, $result['boardsCount']);
        $this->assertEquals($taskAssignedToCount, $result['taskAssignedTo']);
        $this->assertEquals($taskCompletedCount, $result['taskComplete']);
        $this->assertEquals($taskCreatedCount, $result['taskCreated']);
        $this->assertEquals($this->employee->user->locale, $result['language']);
    }
}
