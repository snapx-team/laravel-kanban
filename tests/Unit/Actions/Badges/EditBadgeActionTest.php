<?php

namespace Tests\Unit\Actions\Badges;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Badges\EditBadgeAction;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;

class EditBadgeActionTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        Auth::setUser($this->user);
    }

    public function testEditBadgeActionTest()
    {
        $badgeToEdit = factory(Badge::class)->create(['name' => 'newBadge']);
        $nameBeforeEdit = $badgeToEdit->name;
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $this->user->id]);
        session(['role' => $employee->role, 'employee_id' => $employee->id]);

        app(EditBadgeAction::class)->fill(['badge_id' => $badgeToEdit->id, 'name' => 'newBadgeName'])->run();

        $editedBadge = $badgeToEdit->refresh();

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_BADGE_EDITED,
            'description' => 'Changed badge name from [' . $nameBeforeEdit . '] to [' . $editedBadge->name . ']',
            'targeted_employee_id' => null,
            'loggable_id' => $editedBadge->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Badge'
        ];

        $this->assertEquals('newBadgeName', $editedBadge->name);
        $this->assertDatabaseHas('kanban_logs', $payload);
    }

    public function testEditBadgeActionThrowsValidationOnNullName()
    {
        $this->expectException(ValidationException::class);

        $badgeToEdit = factory(Badge::class)->create(['name' => 'newBadge']);
        $nameBeforeEdit = $badgeToEdit->name;
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $this->user->id]);
        session(['role' => $employee->role, 'employee_id' => $employee->id]);

        app(EditBadgeAction::class)->fill(['badge_id' => $badgeToEdit->id, 'name' => null])->run();

        $nameAfterEdit = ($badgeToEdit->refresh())->name;

        $this->assertEquals($nameBeforeEdit, $nameAfterEdit);
    }

    public function testEditBadgeActionThrowsValidationOnEmptyStringName()
    {
        $this->expectException(ValidationException::class);

        $badgeToEdit = factory(Badge::class)->create(['name' => 'newBadge']);
        $nameBeforeEdit = $badgeToEdit->name;
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $this->user->id]);
        session(['role' => $employee->role, 'employee_id' => $employee->id]);

        app(EditBadgeAction::class)->fill(['badge_id' => $badgeToEdit->id, 'name' => ''])->run();

        $nameAfterEdit = ($badgeToEdit->refresh())->name;

        $this->assertEquals($nameBeforeEdit, $nameAfterEdit);
    }

    public function testEditBadgeActionThrowsValidationWhenExistingBadgeHasTheSameName()
    {
        $this->expectException(ValidationException::class);

        factory(Badge::class)->create(['name' => 'newBadgeName']);
        $badgeToEdit = factory(Badge::class)->create(['name' => 'newBadge']);
        $nameBeforeEdit = $badgeToEdit->name;
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $this->user->id]);
        session(['role' => $employee->role, 'employee_id' => $employee->id]);

        app(EditBadgeAction::class)->fill(['badge_id' => $badgeToEdit->id, 'name' => 'newBadgeName'])->run();

        $nameAfterEdit = ($badgeToEdit->refresh())->name;

        $this->assertEquals($nameBeforeEdit, $nameAfterEdit);
    }

    public function testEditBadgeActionThrowsValidationWhenExistingBadgeHasTheSameNam()
    {
        $this->expectException(AuthorizationException::class);

        $employee = factory(Employee::class)->create(['user_id' => $this->user->id]);
        session(['role' => $employee->role, 'employee_id' => $employee->id]);
        $badgeToEdit = factory(Badge::class)->create(['name' => 'newBadge']);
        $nameBeforeEdit = $badgeToEdit->name;

        app(EditBadgeAction::class)->fill(['badge_id' => $badgeToEdit->id, 'name' => 'newBadgeName'])->run();

        $nameAfterEdit = ($badgeToEdit->refresh())->name;

        $this->assertEquals($nameBeforeEdit, $nameAfterEdit);
    }
}