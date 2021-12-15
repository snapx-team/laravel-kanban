<?php

namespace Tests\Unit\Repositories;

use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Row;
use Xguard\LaravelKanban\Repositories\ColumnsRepository;

class ColumnsRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->columnsRepository = new ColumnsRepository();
    }

    public function testFindColumnReturnsColumnWhenItExists()
    {
        $column = factory(Column::class)->create();
        $result = $this->columnsRepository::findColumn($column->id);
        $this->assertInstanceOf(Column::class, $result);
        $this->assertEquals($result->id, $column->id);
    }

    public function testFindColumnReturnsNullWhenColumnDoesntExist()
    {
        $this->assertNull(($this->columnsRepository::findColumn(1)));
    }

    public function testGetColumnsReturnsAllColumnsInRow()
    {
        $row = factory(Row::class)->create();
        $columns = factory(Column::class, 10)->create(['row_id' => $row->id]);

        $result = $this->columnsRepository::getColumns($row->id);
        
        $this->assertEquals(count($columns), count($result));
    }

    public function testUpdateColumnIndex()
    {
        $newIndex = rand(1, 10);
        $column = factory(Column::class)->create(['index' => 0]);
        $this->assertEquals(0, $column->index);

        $result = $this->columnsRepository::updateColumnIndex($column->id, $newIndex);

        $this->assertEquals($newIndex, $result->index);
        $column->refresh();
        $this->assertEquals($newIndex, $column->index);
    }

    public function testUpdateColumnIndexTrowsException()
    {
        $this->expectException(Exception::class);

        $newIndex = rand(1, 10);
        $randomId = rand(1, 10);
        $this->columnsRepository::updateColumnIndex($randomId, $newIndex);
    }

    public function testDeleteColumn()
    {
        $column = factory(Column::class)->create();
        $this->assertEquals(1, Column::count());
        $this->columnsRepository::deleteColumn($column->id);
        $this->assertEquals(0, Column::count());
    }

    public function testCreateColumn()
    {
        $row = factory(Row::class)->create();
        $payload = [
            'row_id' => $row->id,
            'name' => 'name',
            'index' => 0,
        ];

        $result = $this->columnsRepository::createColumn($payload);

        $this->assertEquals(1, Column::count());
        $this->assertEquals($payload['row_id'], $result->row_id);
        $this->assertEquals($payload['name'], $result->name);
        $this->assertEquals($payload['index'], $result->index);
    }

    public function testUpdateColumn()
    {
        $row = factory(Row::class)->create();
        $column = factory(Column::class)->create(['name' => 'name', 'index' => 0]);

        $this->assertNotEquals($row->id, $column->row_id);

        $payload = [
            'row_id' => $row->id,
            'name' => 'newName',
            'index' => 1,
        ];

        $result = $this->columnsRepository::updateColumn($column->id, $payload);

        $this->assertEquals($payload['row_id'], $result->row_id);
        $this->assertEquals($payload['name'], $result->name);
        $this->assertEquals($payload['index'], $result->index);
    }
}
