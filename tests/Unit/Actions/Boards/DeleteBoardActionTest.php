<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Boards\DeleteBoardAction;
use Xguard\LaravelKanban\Models\Board;

class DeleteBoardActionTest extends TestCase
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
        $board = factory(Board::class)->create();
        app(DeleteBoardAction::class)->fill(
            [
                'boardId' => $board->id
            ])->run();
        $board = Board::all()->first();
        $this->assertNull($board);
    }
}
