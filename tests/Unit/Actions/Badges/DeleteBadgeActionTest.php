<?php

namespace Tests\Unit\Actions\Badges;

use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Badges\DeleteBadgeAction;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\Template;

class DeleteBadgeActionTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        Auth::setUser($user);
    }

    public function testGivenValidIdAndNoTaskOrTemplateThatUsesBadgeThenDeletesBadgeAndCreateLog()
    {
        $badge = factory(Badge::class)->create();

        app(DeleteBadgeAction::class)->fill(['badge_id' => $badge->id])->run();

        $badge->refresh();

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_BADGE_DELETED,
            'description' => 'Deleted badge [' . $badge->name . ']',
            'targeted_employee_id' => null,
            'loggable_id' => $badge->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Badge'
        ];

        $this->assertDatabaseHas('kanban_logs', $payload);
        $this->assertNotNull($badge->deleted_at);
    }

    public function testGivenInvalidIdThenFailsAndCreatesNoLog()
    {
        $this->expectException(ModelNotFoundException::class);

        app(DeleteBadgeAction::class)->fill(['badge_id' => 1])->run();

        $this->assertEquals(0 , Log::count());
    }

    public function testGivenTemplateUsesBadgeThenDontDeleteBadgeAndCreatesNoLog()
    {
        $this->expectException(Exception::class);

        $badge = factory(Badge::class)->create();
        factory(Template::class)->create(['badge_id' => $badge->id]);

        app(DeleteBadgeAction::class)->fill(['badge_id' => $badge->id])->run();

        $badge->refresh();

        $this->assertNull($badge->deleted_at);
        $this->assertEquals(0 , Log::count());
    }

    public function testGivenTaskUsesBadgeThenDontDeleteBadgeAndCreatesNoLog()
    {
        $this->expectException(Exception::class);

        $badge = factory(Badge::class)->create();
        factory(Task::class)->create(['badge_id' => $badge->id]);

        app(DeleteBadgeAction::class)->fill(['badge_id' => $badge->id])->run();

        $badge->refresh();

        $this->assertNull($badge->deleted_at);
        $this->assertEquals(0 , Log::count());
    }
}
