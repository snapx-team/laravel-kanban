<?php

namespace Tests\Unit\Actions\Tasks;

use App\Models\User;
use Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Tasks\UpdateTaskCardIndexesAction;
use Xguard\LaravelKanban\Actions\Tasks\UpdateTaskColumnAndRowAction;
use Xguard\LaravelKanban\Enums\Roles;
use Xguard\LaravelKanban\Enums\SessionVariables;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Row;
use Xguard\LaravelKanban\Models\Task;

class UpdateTaskCardIndexesActionTest extends TestCase
{
    use RefreshDatabase;

    const ADDED = 'added';
    const REMOVED = 'removed';

    private $board;
    private $row;
    private $column;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $user = factory(User::class)->create();
        Auth::setUser($user);
        $employee = factory(Employee::class)->states('admin')->create([Employee::USER_ID => $user->id,]);
        session([
            SessionVariables::ROLE()->getValue() => Roles::ADMIN()->getValue(),
            SessionVariables::EMPLOYEE_ID()->getValue() => $employee->id
        ]);


        $this->board = factory(Column::class)->create();
        $this->row = factory(Row::class)->create([Row::BOARD_ID => $this->board->id]);
        $this->column = factory(Column::class)->create([Column::ROW_ID => $this->row->id]);
    }

    public function testUpdateTaskCardIndexesActionForIndexSort()
    {
        $task1 = factory(Task::class)->create([
            Task::INDEX => 2, Task::ROW_ID => $this->row->id, Task::COLUMN_ID => $this->column->id,
            Task::BOARD_ID => $this->board->id
        ]);
        $task2 = factory(Task::class)->create([
            Task::INDEX => 0, Task::ROW_ID => $this->row->id, Task::COLUMN_ID => $this->column->id,
            Task::BOARD_ID => $this->board->id
        ]);
        $task3 = factory(Task::class)->create([
            Task::INDEX => 1, Task::ROW_ID => $this->row->id, Task::COLUMN_ID => $this->column->id,
            Task::BOARD_ID => $this->board->id
        ]);

        app(UpdateTaskCardIndexesAction::class)->fill([
            UpdateTaskCardIndexesAction::DATA => [
                UpdateTaskCardIndexesAction::TASK_CARDS => [$task1, $task2, $task3],
                UpdateTaskCardIndexesAction::SELECTED_SORT_METHOD => 'index',
                UpdateTaskCardIndexesAction::TARGET_TASK_ID => null,
                UpdateTaskCardIndexesAction::TYPE => self::ADDED
            ]
        ])->run();

        $task1->refresh();
        $task2->refresh();
        $task3->refresh();

        $this->assertEquals($task1->index, 0);
        $this->assertEquals($task2->index, 1);
        $this->assertEquals($task3->index, 2);
    }

    public function testUpdateTaskCardIndexesActionForNonIndexSortAddsNewestCardToLastIndex()
    {
        $task1 = factory(Task::class)->create([
            Task::INDEX => 1, Task::ROW_ID => $this->row->id, Task::COLUMN_ID => $this->column->id,
            Task::BOARD_ID => $this->board->id
        ]);
        $task2 = factory(Task::class)->create([
            Task::INDEX => 0, Task::ROW_ID => $this->row->id, Task::COLUMN_ID => $this->column->id,
            Task::BOARD_ID => $this->board->id
        ]);
        $task3 = factory(Task::class)->create([
            Task::INDEX => 0, Task::ROW_ID => $this->row->id, Task::COLUMN_ID => $this->column->id,
            Task::BOARD_ID => $this->board->id
        ]);

        app(UpdateTaskCardIndexesAction::class)->fill([
            UpdateTaskCardIndexesAction::DATA => [
                UpdateTaskCardIndexesAction::TASK_CARDS => [$task1, $task2, $task3],
                UpdateTaskCardIndexesAction::SELECTED_SORT_METHOD => null,
                UpdateTaskCardIndexesAction::TARGET_TASK_ID => $task3->id,
                UpdateTaskCardIndexesAction::TYPE => self::ADDED
            ]
        ])->run();

        $task1->refresh();
        $task2->refresh();
        $task3->refresh();

        $this->assertEquals($task1->index, 1);
        $this->assertEquals($task2->index, 0);
        $this->assertEquals($task3->index, 2);
    }

    public function testUpdateTaskCardIndexesActionForNonIndexSortWhenRemovedResortsByIndexRegardlessOfOrder()
    {

        $task1 = factory(Task::class)->create([
            Task::INDEX => 3, Task::ROW_ID => $this->row->id, Task::COLUMN_ID => $this->column->id,
            Task::BOARD_ID => $this->board->id
        ]);
        $task2 = factory(Task::class)->create([
            Task::INDEX => 5, Task::ROW_ID => $this->row->id, Task::COLUMN_ID => $this->column->id,
            Task::BOARD_ID => $this->board->id
        ]);
        $task3 = factory(Task::class)->create([
            Task::INDEX => 1, Task::ROW_ID => $this->row->id, Task::COLUMN_ID => $this->column->id,
            Task::BOARD_ID => $this->board->id
        ]);

        app(UpdateTaskCardIndexesAction::class)->fill([
            UpdateTaskCardIndexesAction::DATA => [
                UpdateTaskCardIndexesAction::TASK_CARDS => [$task1, $task2, $task3],
                UpdateTaskCardIndexesAction::SELECTED_SORT_METHOD => null,
                UpdateTaskCardIndexesAction::TARGET_TASK_ID => null,
                UpdateTaskCardIndexesAction::TYPE => self::REMOVED
            ]
        ])->run();

        $task1->refresh();
        $task2->refresh();
        $task3->refresh();

        $this->assertEquals($task1->index, 1);
        $this->assertEquals($task2->index, 2);
        $this->assertEquals($task3->index, 0);
    }
}
