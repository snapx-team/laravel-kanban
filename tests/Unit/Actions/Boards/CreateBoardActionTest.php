<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Boards\CreateBoardAction;
use Xguard\LaravelKanban\Models\Board;

class CreateBoardActionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        Auth::setUser($this->user);
        $this->faker = Faker\Factory::create();
    }

    public function testCreateBoardGivenNewName()
    {
        app(CreateBoardAction::class)->fill(
            [
                'boardName' => $this->faker->name
            ])->run();
        $board = Board::all()->first();
        $this->assertDatabaseHas('kanban_boards',[
            'id' => $board->id,
            'name' => $board->name,
            'created_at' => $board->created_at,
            'updated_at' => $board->updated_at,
            'deleted_at' => $board->deleted_at,
        ]);
    }

    public function testCreateBoardGivenExistingName()
    {
        $board = factory(Board::class)->create();
        $this->expectException(Exception::class);
        app(CreateBoardAction::class)->fill(
            [
                'boardName' => $board->name,
            ])->run();
    }
}
