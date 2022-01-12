<?php

namespace Tests\Unit\Actions\Tasks;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Tasks\PlaceTaskAction;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Row;
use Xguard\LaravelKanban\Models\Task;

class PlaceTaskActionTest extends TestCase
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

    public function testAUserCanPlaceTaskInNewBoardAndRowAndColumn()
    {
        $task = factory(Task::class)->create(['reporter_id' => session('employee_id')]);
        $column = factory(Column::class)->create();

        $this->assertDatabaseMissing('kanban_tasks', [
            'id' => $task->id,
            'board_id' => $column->row->board->id,
            'row_id' => $column->row->id,
            'column_id' => $column->id,
        ]);

        app(PlaceTaskAction::class)->fill([
            'taskId' => $task->id,
            'boardId' => $column->row->board->id,
            'rowId' => $column->row->id,
            'columnId' => $column->id,
        ])->run();

        $this->assertDatabaseHas('kanban_tasks', [
            'id' => $task->id,
            'board_id' => $column->row->board->id,
            'row_id' => $column->row->id,
            'column_id' => $column->id,
        ]);
    }

    public function testAUserCanRemoveTaskPlacement()
    {
        $column = factory(Column::class)->create();
        $row = factory(Row::class)->create();

        $task = factory(Task::class)->create([
            'row_id' => $row->id,
            'column_id' => $column->id,
            'reporter_id' => session('employee_id')
        ]);

        $this->assertDatabaseHas('kanban_tasks', [
            'id' => $task->id,
            'row_id' => $row->id,
            'column_id' => $column->id,
        ]);

        app(PlaceTaskAction::class)->fill([
            'taskId' => $task->id,
            'boardId' => $column->row->board->id,
            'rowId' => null,
            'columnId' => null
        ])->run();

        $this->assertDatabaseHas('kanban_tasks', [
            'id' => $task->id,
            'board_id' => $column->row->board->id,
            'row_id' => null,
            'column_id' => null,
        ]);
    }
}
