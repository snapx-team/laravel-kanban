<?php

namespace Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\EmployeeBoardNotificationSetting;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Repositories\BoardsRepository;

class BoardsRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->boardsRepository = new BoardsRepository();
        $this->employee = factory(Employee::class)->create();
        session(['role' => 'admin', 'employee_id' => $this->employee->id]);
    }

    public function testGetBoardsWithEmployeeNotificationSettings()
    {
        $board = factory(Board::class)->create();
        factory(Member::class)->create(['employee_id' => $this->employee->id, 'board_id' => $board->id]);
        $settings = factory(EmployeeBoardNotificationSetting::class)->create(["board_id" => $board->id, "employee_id" => $this->employee->id]);

        $otherEmployees = factory(Employee::class, 2)->create();
        foreach ($otherEmployees as $otherEmployee) {
            factory(Member::class)->create(['employee_id' => $otherEmployee->id, 'board_id' => $board->id]);
            factory(EmployeeBoardNotificationSetting::class)->create(["board_id" => $board->id, "employee_id" => $otherEmployee->id]);
        }

        $result = $this->boardsRepository::getBoardsWithEmployeeNotificationSettings($this->employee->id);

        $this->assertEquals(count($result), 1);
        $this->assertEquals(count($result[0]["members"]), 3);
        $this->assertNotNull($result[0]->employeeNotificationSettings); //returns the settings of the session's employee
        $this->assertEquals($result[0]->employeeNotificationSettings->employee->id, $settings->employee->id);
    }

    public function testGetBoards()
    {
        $board = factory(Board::class)->create(['deleted_at' => null]);
        $retrievedBoard = $this->boardsRepository::getBoards();
        $this->assertDatabaseHas('kanban_boards', [
            'id' => $retrievedBoard[0]->id,
            'name' => $retrievedBoard[0]->name,
            'created_at' => $retrievedBoard[0]->created_at,
            'updated_at' => $retrievedBoard[0]->updated_at,
            'deleted_at' => $retrievedBoard[0]->deleted_at,
        ]);
        $this->assertEquals($board->name, $retrievedBoard[0]->name);
    }
}
