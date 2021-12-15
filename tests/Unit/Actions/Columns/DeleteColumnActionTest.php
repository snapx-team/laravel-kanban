<?php

namespace Tests\Unit\Actions\Columns;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Columns\DeleteColumnAction;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Log;

class DeleteColumnActionTest extends TestCase
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
        $column = factory(Column::class)->create();

        app(DeleteColumnAction::class)->fill(['columnId' => $column->id])->run();

        $column->refresh();

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_COLUMN_DELETED,
            'description' => 'Deleted  column [' . $column->name . '] from row [' . $column->row->name . '] from board [' . $column->row->board->name . ']',
            'targeted_employee_id' => null,
            'loggable_id' => $column->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Column'
        ];

        $this->assertDatabaseHas('kanban_logs', $payload);
        $this->assertNotNull($column->deleted_at);
    }

    public function testGivenInvalidIdThenFailsAndCreatesNoLog()
    {
        $this->expectException(Exception::class);

        app(DeleteColumnAction::class)->fill(['columnId' => 1])->run();

        $this->assertEquals(0 , Log::count());
    }

    public function testGivenInvalidIdRuleThenFailsAndThrowsValidationError()
    {
        $this->expectException(ValidationException::class);

        app(DeleteColumnAction::class)->fill(['columnId' => null])->run();

        $this->assertEquals(0 , Log::count());
    }
}
