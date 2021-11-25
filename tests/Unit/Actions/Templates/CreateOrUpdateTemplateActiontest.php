<?php

namespace Tests\Unit\Actions\Templates;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Templates\CreateOrUpdateTemplateAction;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Template;

class CreateOrUpdateTemplateActionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $user->id,]);
        Auth::setUser($user);
        session(['role' => 'admin', 'employee_id' => $employee->id]);
    }

    public function testCreateOrUpdateTemplateActionCreatesBadgeIfNoBadgeProvided()
    {
        app(CreateOrUpdateTemplateAction::class)->fill([
            'description' => 'description',
            'name' => 'name',
            'taskName' => 'taskName',
            'templateId' => null,
            'badge' => ['name' => 'badgeName'],
            'boards' => [],
            'checkedOptions' => [],
        ])->run();
        $this->assertEquals(1, Badge::count());
    }

    public function testCreateOrUpdateTemplateActionUsesBadgeIfOneProvided()
    {
        factory(Badge::class)->create(['name' => 'badgeName']);

        $this->assertEquals(1, Badge::count());

        app(CreateOrUpdateTemplateAction::class)->fill([
            'description' => 'description',
            'name' => 'name',
            'taskName' => 'taskName',
            'templateId' => null,
            'badge' => ['name' => 'badgeName'],
            'boards' => [],
            'checkedOptions' => [],
        ])->run();

        $this->assertEquals(1, Badge::count());
    }

    public function testCreateOrUpdateTemplateActionCreatesTemplateIfNoTemplateIdIsProvided()
    {
        $this->assertEquals(0, Template::count());

        $template = app(CreateOrUpdateTemplateAction::class)->fill([
            'description' => 'description',
            'name' => 'name',
            'taskName' => 'taskName',
            'templateId' => null,
            'badge' => [],
            'boards' => [],
            'checkedOptions' => [],
        ])->run();

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_TEMPLATE_CREATED,
            'description' => 'Created template [' .$template->name. ']',
            'targeted_employee_id' => null,
            'loggable_id' => $template->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Template'
        ];

        $this->assertEquals(1, Template::count());
        $this->assertDatabaseHas('kanban_logs', $payload);
    }

    public function testCreateOrUpdateTemplateActionUpdatesTemplateIfTemplateIdIsProvided()
    {
        $template = factory(Template::class)->create();
        $this->assertEquals(1, Template::count());

        $template = app(CreateOrUpdateTemplateAction::class)->fill([
            'description' => 'newDescription',
            'name' => 'newName',
            'taskName' => 'newTaskName',
            'templateId' => $template->id,
            'badge' => [],
            'boards' => [],
            'checkedOptions' => [],
        ])->run();

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_TEMPLATE_UPDATED,
            'description' => 'Updated template [' .$template->name. ']',
            'targeted_employee_id' => null,
            'loggable_id' => $template->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Template'
        ];

        $this->assertEquals(1, Template::count());
        $this->assertDatabaseHas('kanban_logs', $payload);
        $this->assertDatabaseHas('kanban_Templates', [
            'description' => 'newDescription',
            'name' => 'newName',
            'task_name' => 'newTaskName',
            'id' => $template->id,
            'badge_id' => $template->badge->id,
            'options' => serialize([]),
        ]);
    }
}
