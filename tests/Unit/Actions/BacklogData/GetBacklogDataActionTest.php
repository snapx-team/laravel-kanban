<?php

namespace Tests\Unit;

use App\Models\User;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\BacklogData\GetBacklogDataAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Row;
use Xguard\LaravelKanban\Models\Task;

class GetBacklogDataActionTest extends TestCase
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

    public function testGetBacklogData()
    {
        $board = factory(Board::class)->create();

        $row = factory(Row::class)->create(['board_id'=> $board->id]);
        $column = factory(Column::class)->create(['row_id'=> $row->id]);

        factory(Task::class, 5)->create([
            'board_id' => $board->id,
            'row_id' => $row->id,
            'column_id' => $column->id
        ]);

        factory(Task::class, 5)->create([
            'board_id' => $board->id,
            'row_id' => null,
            'column_id' => null,
        ]);

        factory(Task::class, 5)->create([
            'board_id' => $board->id,
            'status' => 'cancelled',
            'row_id' => null,
            'column_id' => null,
        ]);

        factory(Task::class, 5)->create([
            'board_id' => $board->id,
            'status' => 'completed',
            'row_id' => null,
            'column_id' => null,
        ]);

        $end = new DateTime('now');
        $start = new DateTime('yesterday');

        $backlogData = app(GetBacklogDataAction::class)->fill([
            'start' => $start->format('Y-m-d H:i:s'), 'end' => $end->format('Y-m-d H:i:s'),
        ])->run();

        $this->assertEquals(20, $backlogData['boards'][$board->id]->total);
        $this->assertEquals(10, $backlogData['boards'][$board->id]->active);
        $this->assertEquals(5, $backlogData['boards'][$board->id]->completed);
        $this->assertEquals(5, $backlogData['boards'][$board->id]->cancelled);
        $this->assertEquals(5, $backlogData['boards'][$board->id]->placed_in_board);
        $this->assertCount(1, $backlogData['allBoards']);
    }
}
