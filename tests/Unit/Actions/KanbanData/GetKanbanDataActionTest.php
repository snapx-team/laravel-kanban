<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\KanbanData\GetKanbanDataAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;

class GetKanbanDataActionTest extends TestCase
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

    public function testGetKanbanDataIfBoardExists()
    {
        $board= factory(Board::class)->create();


        $kanbanData =  app(GetKanbanDataAction::class)->fill([
            'boardId' => $board->id,
        ])->run();

        $this->assertNotNull($kanbanData);
    }

    public function testGetKanbanDataIsNullIfBoardDoesntExist()
    {
        $kanbanData =  app(GetKanbanDataAction::class)->fill([
            'boardId' => 100,
        ])->run();

        $this->assertNull($kanbanData);
    }
}
