<?php

namespace Tests\Unit\Actions\Columns;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Columns\UpdateColumIndexesAction;
use Xguard\LaravelKanban\Models\Column;

class UpdateColumIndexesActionTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        Auth::setUser($this->user);
    }

    public function testGivenValidIdThenDeletesRowAndCreateLog()
    {
        $column = factory(Column::class)->create(['index' => rand(10, 20)]);
        $column2 = factory(Column::class)->create(['index' => rand(10, 20)]);

        app(UpdateColumIndexesAction::class)->fill([
            'columns' => [
                ['id' => $column->id],
                ['id' => $column2->id],
            ]
        ])->run();

        $column->refresh();
        $column2->refresh();

        $this->assertEquals(0 , $column->index);
        $this->assertEquals(1 , $column2->index);
    }
}
