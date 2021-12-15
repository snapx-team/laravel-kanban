<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\KanbanData\GetKanbanHashAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Row;

class GetKanbanHashActionTest extends TestCase
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

    public function testGetKanbanDataHashIfBoardExists()
    {
        $board= factory(Board::class)->create();


        $kanbanHash =  app(GetKanbanHashAction::class)->fill([
            'boardId' => $board->id,
        ])->run();

        $this->assertNotNull($kanbanHash);
    }

    public function testKanbanHashStaysTheSameIFNoChangeHappens()
    {
        $board= factory(Board::class)->create();


        $kanbanHashBefore =  app(GetKanbanHashAction::class)->fill([
            'boardId' => $board->id,
        ])->run();

        $kanbanHashAfter =  app(GetKanbanHashAction::class)->fill([
            'boardId' => $board->id,
        ])->run();

        $this->assertEquals($kanbanHashBefore, $kanbanHashAfter);
    }

    public function testKanbanHashChangesIfKanbanDataChanges()
    {
        $board= factory(Board::class)->create();
        $kanbanHashBefore =  app(GetKanbanHashAction::class)->fill([
            'boardId' => $board->id,
        ])->run();

        factory(Row::class)->create(['board_id' => $board->id]);
        $kanbanHashAfter =  app(GetKanbanHashAction::class)->fill([
            'boardId' => $board->id,
        ])->run();

        $this->assertNotEquals($kanbanHashBefore, $kanbanHashAfter);
    }
}
