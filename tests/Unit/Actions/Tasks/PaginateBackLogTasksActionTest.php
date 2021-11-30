<?php

namespace Tests\Unit\Actions\Tasks;

use App\Models\User;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Tasks\PaginateBackLogTasksAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;

class PaginateBackLogTasksActionTest extends TestCase
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

    public function testAUserCanGetBacklogTasks()
    {
        factory(Task::class, 25)->create(['reporter_id' => session('employee_id')]);
        $now = new DateTime('now');

        $retrievedTasks = app(PaginateBackLogTasksAction::class)->fill([
            'filterStart' => $this->faker->dateTime('yesterday')->format('Y-m-d H:i:s'),
            'filterEnd' => $now->format('Y-m-d H:i:s'),
            'filterStatus' => ['active', 'completed', 'cancelled'],
            'filterPlacedInBoard' => true,
            'filterNotPlacedInBoard' => true,
            'filterText' => null,
            'filterBadge' => [],
            'filterBoard' => [],
            'filterAssignedTo' => [],
            'filterReporter' => [],
            'filterErpEmployee' => [],
            'filterErpContract' => [],
        ])->run();

        $this->assertCount(15, $retrievedTasks);
    }

    public function testRetrieveTaskBasedOnStatus()
    {
        factory(Task::class, 1)->create(['reporter_id' => session('employee_id'), 'status' => 'active']);
        factory(Task::class, 2)->create(['reporter_id' => session('employee_id'), 'status' => 'cancelled']);

        $now = new DateTime('now');
        $yesterday = new DateTime('yesterday');

        $retrievedTasks = app(PaginateBackLogTasksAction::class)->fill([
            'filterStart' => $yesterday->format('Y-m-d H:i:s'),
            'filterEnd' => $now->format('Y-m-d H:i:s'),
            'filterStatus' => ['active'],
            'filterPlacedInBoard' => true,
            'filterNotPlacedInBoard' => true,
            'filterText' => null,
            'filterBadge' => [],
            'filterBoard' => [],
            'filterAssignedTo' => [],
            'filterReporter' => [],
            'filterErpEmployee' => [],
            'filterErpContract' => [],
        ])->run();

        $this->assertCount(1, $retrievedTasks);
    }

    public function testRetrieveTaskBasedOnPlacedInBoard()
    {
        $column = factory(Column::class)->create();

        factory(Task::class, 5)->create(['reporter_id' => session('employee_id'), 'column_id' => $column->id]);
        factory(Task::class, 10)->create(['reporter_id' => session('employee_id'), 'column_id' => null]);

        $now = new DateTime('now');
        $yesterday = new DateTime('yesterday');

        $retrievedTasks = app(PaginateBackLogTasksAction::class)->fill([
            'filterStart' => $yesterday->format('Y-m-d H:i:s'),
            'filterEnd' => $now->format('Y-m-d H:i:s'),
            'filterStatus' => ['active'],
            'filterPlacedInBoard' => true,
            'filterNotPlacedInBoard' => false,
            'filterText' => null,
            'filterBadge' => [],
            'filterBoard' => [],
            'filterAssignedTo' => [],
            'filterReporter' => [],
            'filterErpEmployee' => [],
            'filterErpContract' => [],
        ])->run();

        $this->assertCount(5, $retrievedTasks);

        $retrievedTasks = app(PaginateBackLogTasksAction::class)->fill([
            'filterStart' => $this->faker->dateTime('yesterday')->format('Y-m-d H:i:s'),
            'filterEnd' => $now->format('Y-m-d H:i:s'),
            'filterStatus' => ['active'],
            'filterPlacedInBoard' => false,
            'filterNotPlacedInBoard' => true,
            'filterText' => null,
            'filterBadge' => [],
            'filterBoard' => [],
            'filterAssignedTo' => [],
            'filterReporter' => [],
            'filterErpEmployee' => [],
            'filterErpContract' => [],
        ])->run();

        $this->assertCount(10, $retrievedTasks);
    }

    public function testRetrieveTaskBasedOnText()
    {
        $board = factory(Board::class)->create(['name' => 'board']);
        $task1 = factory(Task::class)->create(['reporter_id' => session('employee_id'), 'name' => 'task']);
        $task2 = factory(Task::class)->create(['reporter_id' => session('employee_id'), 'board_id' => $board]);

        $now = new DateTime('now');
        $yesterday = new DateTime('yesterday');

        //match task name
        $retrievedTasks1 = app(PaginateBackLogTasksAction::class)->fill([
            'filterStart' => $yesterday->format('Y-m-d H:i:s'),
            'filterEnd' => $now->format('Y-m-d H:i:s'),
            'filterStatus' => ['active'],
            'filterPlacedInBoard' => true,
            'filterNotPlacedInBoard' => true,
            'filterText' => $task1->name,
            'filterBadge' => [],
            'filterBoard' => [],
            'filterAssignedTo' => [],
            'filterReporter' => [],
            'filterErpEmployee' => [],
            'filterErpContract' => [],
        ])->run();

        //match board name
        $retrievedTasks2 = app(PaginateBackLogTasksAction::class)->fill([
            'filterStart' => $yesterday->format('Y-m-d H:i:s'),
            'filterEnd' => $now->format('Y-m-d H:i:s'),
            'filterStatus' => ['active'],
            'filterPlacedInBoard' => true,
            'filterNotPlacedInBoard' => true,
            'filterText' => $task2->board->name,
            'filterBadge' => [],
            'filterBoard' => [],
            'filterAssignedTo' => [],
            'filterReporter' => [],
            'filterErpEmployee' => [],
            'filterErpContract' => [],
        ])->run();

        //match id
        $retrievedTasks3 = app(PaginateBackLogTasksAction::class)->fill([
            'filterStart' => $yesterday->format('Y-m-d H:i:s'),
            'filterEnd' => $now->format('Y-m-d H:i:s'),
            'filterStatus' => ['active'],
            'filterPlacedInBoard' => true,
            'filterNotPlacedInBoard' => true,
            'filterText' => (String)$task2->id,
            'filterBadge' => [],
            'filterBoard' => [],
            'filterAssignedTo' => [],
            'filterReporter' => [],
            'filterErpEmployee' => [],
            'filterErpContract' => [],
        ])->run();

        //match simple task name
        $retrievedTasks4 = app(PaginateBackLogTasksAction::class)->fill([
            'filterStart' => $yesterday->format('Y-m-d H:i:s'),
            'filterEnd' => $now->format('Y-m-d H:i:s'),
            'filterStatus' => ['active'],
            'filterPlacedInBoard' => true,
            'filterNotPlacedInBoard' => true,
            'filterText' => 'boa-2',
            'filterBadge' => [],
            'filterBoard' => [],
            'filterAssignedTo' => [],
            'filterReporter' => [],
            'filterErpEmployee' => [],
            'filterErpContract' => [],
        ])->run();
        $this->assertCount(1, $retrievedTasks1);
        $this->assertCount(1, $retrievedTasks2);
        $this->assertCount(1, $retrievedTasks3);
        $this->assertCount(1, $retrievedTasks4);
    }
}
