<?php

namespace Tests\Unit\Actions\Rows;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Rows\CreateOrUpdateRowAndColumnsAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Row;

class CreateOrUpdateRowAndColumnsActionTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        Auth::setUser($this->user);
    }

    public function testCreateOrUpdateRowAndColumnsActionCreatesNewEmptyRowGivenEmptySentColumns()
    {
        $board = factory(Board::class)->create();
        
        $result = app(CreateOrUpdateRowAndColumnsAction::class)->fill([
            'rowId' => null,
            'name' => 'name',
            'rowIndex' => 0,
            'boardId' => $board->id,
            'sentColumns' => []
        ])->run();

        $newRow = Row::first();

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_ROW_CREATED,
            'description' => 'Created row [' . $newRow->name . '] in board [' . $newRow->board->name . ']',
            'targeted_employee_id' => null,
            'loggable_id' => $newRow->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Row'
        ];

        $this->assertEquals(1, count($result));
        $this->assertEmpty($result[0]->columns);
        $this->assertEquals(1, Row::count());
        $this->assertEquals(0, Column::count());
        $this->assertDatabaseHas('kanban_logs', $payload);
    }

    public function testCreateOrUpdateRowAndColumnsActionUpdatesRowInfo()
    {
        $row = factory(Row::class)->create();
        
        $result = app(CreateOrUpdateRowAndColumnsAction::class)->fill([
            'rowId' => $row->id,
            'name' => 'newName',
            'rowIndex' => 0,
            'boardId' => $row->board->id,
            'sentColumns' => []
        ])->run();

        $row->refresh();

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_ROW_UPDATED,
            'description' => 'Updated row [' . $row->name . '] in board [' . $row->board->name . ']',
            'targeted_employee_id' => null,
            'loggable_id' => $row->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Row'
        ];

        $this->assertEquals(1, count($result));
        $this->assertEmpty($result[0]->columns);
        $this->assertEquals(1, Row::count());
        $this->assertEquals(0, Column::count());
        $this->assertEquals('newName', $row->name);
        $this->assertEquals(0, $row->rowIndex);
        $this->assertDatabaseHas('kanban_logs', $payload);
    }

    public function testCreateOrUpdateRowAndColumnsActionCreatesColumnsOnNewRowWhenCreatingRow()
    {
        $board = factory(Board::class)->create();
        
        $result = app(CreateOrUpdateRowAndColumnsAction::class)->fill([
            'rowId' => null,
            'name' => 'newName',
            'rowIndex' => 0,
            'boardId' => $board->id,
            'sentColumns' => [
                [
                    'id' => null,
                    'name' => 'newColumn'
                ],
            ]
        ])->run();

        $newRow = Row::first();
        $newColumn = Column::first();

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_COLUMN_CREATED,
            'description' => 'Created column [' . $newColumn->name . '] in row [' . $newColumn->row->name . '] in board [' . $newColumn->row->board->name . ']',
            'targeted_employee_id' => null,
            'loggable_id' => $newColumn->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Column'
        ];

        $this->assertEquals(1, Row::count());
        $this->assertEquals(0, $newRow->rowIndex);

        $this->assertEquals(1, Column::count());
        $this->assertEquals($newRow->id, $newColumn->row_id);
        $this->assertEquals(0, $newColumn->index);
        $this->assertEquals('newColumn', $newColumn->name);
        $this->assertEquals($result[0]->columns[0]->id, $newColumn->id);

        $this->assertDatabaseHas('kanban_logs', $payload);
    }

    public function testCreateOrUpdateRowAndColumnsActionCreatesColumnsOnRowWhenUpdatingRow()
    {
        $row = factory(Row::class)->create();
        
        $result = app(CreateOrUpdateRowAndColumnsAction::class)->fill([
            'rowId' => $row->id,
            'name' => 'newName',
            'rowIndex' => 0,
            'boardId' => $row->board->id,
            'sentColumns' => [
                [
                    'id' => null,
                    'name' => 'newColumn'
                ],
            ]
        ])->run();

        $newColumn = Column::first();

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_COLUMN_CREATED,
            'description' => 'Created column [' . $newColumn->name . '] in row [' . $newColumn->row->name . '] in board [' . $newColumn->row->board->name . ']',
            'targeted_employee_id' => null,
            'loggable_id' => $newColumn->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Column'
        ];

        $this->assertEquals(1, Row::count());
        $this->assertEquals(0, $row->rowIndex);

        $this->assertEquals(1, Column::count());
        $this->assertEquals($row->id, $newColumn->row_id);
        $this->assertEquals(0, $newColumn->index);
        $this->assertEquals('newColumn', $newColumn->name);
        $this->assertEquals($result[0]->columns[0]->id, $newColumn->id);

        $this->assertDatabaseHas('kanban_logs', $payload);
    }

    public function testCreateOrUpdateRowAndColumnsActionUpdatesColumnsOnUpdateWhenCreatingRow()
    {
        $row = factory(Row::class)->create();
        $column = factory(Column::class)->create(['name' => 'columnToUpdate', 'row_id' =>$row->id]);

        $result = app(CreateOrUpdateRowAndColumnsAction::class)->fill([
            'rowId' => $row->id,
            'name' => 'newName',
            'rowIndex' => 0,
            'boardId' => $row->board->id,
            'sentColumns' => [
                [
                    'id' => $column->id,
                    'name' => 'newColumnName'
                ],
            ]
        ])->run();

        $column->refresh();
        $this->assertEquals('newColumnName', $column->name);
    }

    public function testCreateOrUpdateRowAndColumnsActionDeletesColumnsOnUpdateWhenCreatingRow()
    {
        $row = factory(Row::class)->create();
        $column = factory(Column::class)->create(['row_id' =>$row->id]);
        
        $result = app(CreateOrUpdateRowAndColumnsAction::class)->fill([
            'rowId' => $row->id,
            'name' => 'newName',
            'rowIndex' => 0,
            'boardId' => $row->board->id,
            'sentColumns' => []
        ])->run();

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_COLUMN_DELETED,
            'description' => 'Deleted column [' . $column->name . '] from row [' . $column->row->name . '] from board [' . $column->row->board->name . ']',
            'targeted_employee_id' => null,
            'loggable_id' => $column->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Column'
        ];

        $this->assertEquals(0, Column::count());
        $this->assertEmpty($result[0]->columns);
        $this->assertDatabaseHas('kanban_logs', $payload);
    }
}
