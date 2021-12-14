<?php

namespace Tests\Unit\Actions\Rows;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Rows\DeleteRowAction;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Row;

class DeleteRowActionTest extends TestCase
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
        $row = factory(Row::class)->create();

        app(DeleteRowAction::class)->fill(['rowId' => $row->id])->run();

        $row->refresh();

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_ROW_DELETED,
            'description' => 'Deleted row [' . $row->name . '] from board [' . $row->board->name . ']',
            'targeted_employee_id' => null,
            'loggable_id' => $row->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Row'
        ];

        $this->assertDatabaseHas('kanban_logs', $payload);
        $this->assertNotNull($row->deleted_at);
    }

    public function testGivenInvalidIdThenFailsAndCreatesNoLog()
    {
        $this->expectException(Exception::class);

        app(DeleteRowAction::class)->fill(['rowId' => 1])->run();

        $this->assertEquals(0 , Log::count());
    }

    public function testGivenInvalidIdRuleThenFailsAndThrowsValidationError()
    {
        $this->expectException(ValidationException::class);

        app(DeleteRowAction::class)->fill(['rowId' => null])->run();

        $this->assertEquals(0 , Log::count());
    }
}
