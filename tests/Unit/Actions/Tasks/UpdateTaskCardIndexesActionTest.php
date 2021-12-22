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
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Row;
use Xguard\LaravelKanban\Models\Task;

class UpdateTaskCardIndexesActionTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $user = factory(User::class)->create();
        Auth::setUser($user);
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $user->id,]);
        session(['role' => 'admin', 'employee_id' => $employee->id]);
    }

    public function testUpdateTaskCardIndexesActionUpdatesItself()
    {

        $board = factory(Column::class)->create();
        $row = factory(Row::class)->create(['board_id'=>$board->id]);
        $column = factory(Column::class)->create(['row_id'=>$row->id]);

        $task1 = factory(Task::class)->create(['index' => 0, 'row_id' => $row->id, 'column_id' => $column->id, 'board_id' => $board->id]);
        $task2 = factory(Task::class)->create(['index' => 0, 'row_id' => $row->id, 'column_id' => $column->id, 'board_id' => $board->id]);
        $task3 = factory(Task::class)->create(['index' => 0, 'row_id' => $row->id, 'column_id' => $column->id, 'board_id' => $board->id]);

        app(UpdateTaskCardIndexesAction::class)->fill([
            'taskCards' => [$task1, $task2, $task3]
        ])->run();

        $task1->refresh();
        $task2->refresh();
        $task3->refresh();

        $this->assertEquals($task1->index, 0);
        $this->assertEquals($task2->index, 1);
        $this->assertEquals($task3->index, 2);
    }
}


