<?php

namespace Tests\Unit\Actions\Badges;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Badges\CreateBadgeAction;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;

class CreateBadgeActionTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        Auth::setUser($this->user);
    }

    public function testCreateBadgeActionTest()
    {
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $this->user->id]);
        session(['role' => $employee->role, 'employee_id' => $employee->id]);

        $badge = app(CreateBadgeAction::class)->fill(['name' => 'newBadge'])->run();

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_BADGE_CREATED,
            'description' => 'Added new badge [' . $badge->name . ']',
            'targeted_employee_id' => null,
            'loggable_id' => $badge->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Badge'
        ];

        $this->assertDatabaseHas('kanban_badges', ['name' => 'newBadge']);
        $this->assertDatabaseHas('kanban_logs', $payload);
    }

    public function testCreateBadgeActionThrowsValidationOnNullName()
    {
        $this->expectException(ValidationException::class);

        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $this->user->id]);
        session(['role' => $employee->role, 'employee_id' => $employee->id]);

        app(CreateBadgeAction::class)->fill(['name' => null])->run();

        $this->assertDatabaseMissing('kanban_badges', ['name' => 'newBadge']);
    }

    public function testCreateBadgeActionThrowsValidationOnEmptyStringName()
    {
        $this->expectException(ValidationException::class);

        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $this->user->id]);
        session(['role' => $employee->role, 'employee_id' => $employee->id]);

        app(CreateBadgeAction::class)->fill(['name' => ''])->run();

        $this->assertDatabaseMissing('kanban_badges', ['name' => 'newBadge']);
    }

    public function testCreateBadgeActionThrowsValidationWhenExistingBadgeHasTheSameName()
    {
        $this->expectException(ValidationException::class);

        $badge = factory(Badge::class)->create(['name' => 'newBadge']);
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $this->user->id]);
        session(['role' => $employee->role, 'employee_id' => $employee->id]);

        app(CreateBadgeAction::class)->fill(['name' => $badge->name])->run();

        $this->assertEquals(1, Badge::where('name', 'newBadge'));
    }

    public function testCreateBadgeActionThrowsAuthorizationErrorWhenUserIsntAdmin()
    {
        $this->expectException(AuthorizationException::class);

        $employee = factory(Employee::class)->create(['user_id' => $this->user->id]);
        session(['role' => $employee->role, 'employee_id' => $employee->id]);

        app(CreateBadgeAction::class)->fill(['name' => 'newBadge'])->run();

        $this->assertDatabaseMissing('kanban_badges', ['name' => 'newBadge']);
    }
}
