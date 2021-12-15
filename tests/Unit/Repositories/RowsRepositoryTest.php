<?php

namespace Tests\Unit\Repositories;

use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Row;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Repositories\RowsRepository;

class RowsRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->rowsRepository = new RowsRepository();
    }

    public function testFindRowReturnsRowWhenItExists()
    {
        $row = factory(Row::class)->create();
        $result = $this->rowsRepository::findRow($row->id);
        $this->assertInstanceOf(Row::class, $result);
        $this->assertEquals($result->id, $row->id);
    }

    public function testFindRowReturnsNullWhenRowDoesntExist()
    {
        $this->assertNull($this->rowsRepository::findRow(1));
    }

    public function testGetRowsReturnsAllRowsInBoard()
    {
        $board = factory(Board::class)->create();
        $rows = factory(Row::class, 10)->create(['board_id' => $board->id]);

        $result = $this->rowsRepository::getRows($board->id);
        
        $this->assertEquals(count($rows), count($result));
    }

    public function testGetRowReturnsRowsWithColumnsInBoard()
    {
        $row = factory(Row::class)->create();
        $columns = factory(Column::class, 10)->create(['row_id' => $row->id]);

        $result = $this->rowsRepository::getRowWithColumns($row->id);

        $this->assertEquals(1, count($result));
        $this->assertEquals(count($columns), count($result[0]->columns));
    }

    public function testGetRowsReturnsAllRowsWithColumnsTasksInBoard()
    {
        $row = factory(Row::class)->create();
        $columns = factory(Column::class, 5)->create(['row_id' => $row->id]);

        foreach ($columns as $column){
            factory(Task::class)->create(['column_id' => $column->id]);
        }

        $result = $this->rowsRepository::getRowWithColumnsTaskCards($row->id);

        $this->assertEquals(1, count($result));

        for ($i = 0 ; $i < 5; $i++) {
            $this->assertEquals(1, count($result[0]->columns[$i]->taskCards));
        }
    }

    public function testUpdateRowIndex()
    {
        $newIndex = rand(1, 10);
        $row = factory(Row::class)->create(['index' => 0]);
        $this->assertEquals(0, $row->index);

        $result = $this->rowsRepository::updateRowIndex($row->id, $newIndex);

        $this->assertEquals($newIndex, $result->index);
        $row->refresh();
        $this->assertEquals($newIndex, $row->index);
    }

    public function testUpdateRowIndexTrowsException()
    {
        $this->expectException(Exception::class);

        $newIndex = rand(1, 10);
        $randomId = rand(1, 10);
        $this->rowsRepository::updateRowIndex($randomId, $newIndex);
    }

    public function testDeleteRow()
    {
        $row = factory(Row::class)->create();
        $this->assertEquals(1, Row::count());
        $this->rowsRepository::deleteRow($row->id);
        $this->assertEquals(0, Row::count());
    }

    public function testCreateRow()
    {
        $board = factory(Board::class)->create();
        $payload = [
            'board_id' => $board->id,
            'name' => 'name',
            'index' => 0,
        ];

        $result = $this->rowsRepository::createRow($payload);

        $this->assertEquals(1, Row::count());
        $this->assertEquals($payload['board_id'], $result->board_id);
        $this->assertEquals($payload['name'], $result->name);
        $this->assertEquals($payload['index'], $result->index);
    }

    public function testUpdateRow()
    {
        $board = factory(Board::class)->create();
        $row = factory(Row::class)->create(['name' => 'name', 'index' => 0]);

        $this->assertNotEquals($board->id, $row->board_id);

        $payload = [
            'board_id' => $board->id,
            'name' => 'newName',
            'index' => 1,
        ];

        $result = $this->rowsRepository::updateRow($row->id, $payload);

        $this->assertEquals($payload['board_id'], $result->board_id);
        $this->assertEquals($payload['name'], $result->name);
        $this->assertEquals($payload['index'], $result->index);
    }
}
