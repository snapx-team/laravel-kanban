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
use Xguard\LaravelKanban\Actions\Tasks\CreateTaskAction;
use Xguard\LaravelKanban\Actions\Tasks\PlaceTaskAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Comment;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Row;
use Xguard\LaravelKanban\Models\Task;

class CreateTaskActionTest extends TestCase
{

    use WithFaker, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        Auth::setUser($user);

        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $user->id,]);
        session(['role' => 'admin', 'employee_id' => $employee->id]);
    }

    public function testAUserCanCreateABacklogTask()
    {
        $board = factory(Board::class)->create();

        $this->assertCount(0, Task::all());

        app(CreateTaskAction::class)->fill([
            'assignedTo' => null,
            'associatedTask' => null,
            'badge' => [],
            'columnId' => null,
            'deadline' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
            'description' => $this->faker->sentence(10),
            'erpEmployees' => [],
            'erpContracts' => [],
            'name' => $this->faker->realText(),
            'rowId' => null,
            'selectedKanbans' => [$board],
        ])->run();

        $this->assertCount(1, Task::all());
    }

    public function testMultipleBacklogTasksCanBeCreated()
    {
        $boards = json_decode(factory(Board::class, 3)->create(), true);

        $this->assertCount(0, Task::all());

        app(CreateTaskAction::class)->fill([
            'assignedTo' => null,
            'associatedTask' => null,
            'badge' => [],
            'columnId' => null,
            'deadline' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
            'description' => $this->faker->sentence(10),
            'erpEmployees' => [],
            'erpContracts' => [],
            'name' => $this->faker->realText(),
            'rowId' => null,
            'selectedKanbans' => $boards,
        ])->run();

        $this->assertCount(3, Task::all());
    }
}
