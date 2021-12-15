<?php

namespace Tests\Unit\Actions\Rows;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Rows\UpdateRowIndexesAction;
use Xguard\LaravelKanban\Models\Row;

class UpdateRowIndexesActionTest extends TestCase
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
        $row = factory(Row::class)->create(['index' => rand(10, 20)]);
        $row2 = factory(Row::class)->create(['index' => rand(10, 20)]);

        app(UpdateRowIndexesAction::class)->fill([
            'rows' => [
                ['id' => $row->id],
                ['id' => $row2->id],
            ]
        ])->run();

        $row->refresh();
        $row2->refresh();

        $this->assertEquals(0 , $row->index);
        $this->assertEquals(1 , $row2->index);
    }
}
