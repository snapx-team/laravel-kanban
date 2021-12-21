<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Boards\EditBoardAction;
use Xguard\LaravelKanban\Models\Board;

class EditBoardActionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        Auth::setUser($this->user);
        $this->faker = Faker\Factory::create();
    }

    public function testEditBoardGivenNewName()
    {
        $board = factory(Board::class)->create();
        app(EditBoardAction::class)->fill(
            [
                'boardId' => $board->id,
                'boardName' => $this->faker->name
            ])->run();
        $boardEdit = Board::all()->first();
        $this->assertDatabaseHas('kanban_boards',[
            'id' => $boardEdit->id,
            'name' => $boardEdit->name,
            'created_at' => $boardEdit->created_at,
            'updated_at' => $boardEdit->updated_at,
            'deleted_at' => $boardEdit->deleted_at,
        ]);
        $this->assertNotSame($board,$boardEdit);
    }
}
