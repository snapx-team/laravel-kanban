<?php

namespace Tests\Unit\Actions\Templates;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Templates\DeleteTemplateAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Template;

class DeleteTemplateActionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->employee = factory(Employee::class)->states('admin')->create(['user_id' => $this->user->id]);
        Auth::setUser($this->user);
        session(['role' => 'admin', 'employee_id' => $this->user->id]);
    }

    public function testDeletionOfTemplate()
    {
        $template = factory(Template::class)->create();

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_TEMPLATE_DELETED,
            'description' => 'Deleted template [' .$template->name. ']',
            'targeted_employee_id' => null,
            'loggable_id' => $template->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Template'
        ];
        
        app(DeleteTemplateAction::class)->fill(['templateId' => $template->id])->run();
        
        $this->assertDatabaseHas('kanban_logs', $payload);    
        $this->assertNull(Template::where('id', $template->id)->first());
    }

    public function testErrorIsThrownWhenAnInvalidIdIsGiven()
    {
        $this->expectException(Exception::class);
        $template = factory(Template::class)->create();

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_TEMPLATE_DELETED,
            'description' => 'Deleted template [' .$template->name. ']',
            'targeted_employee_id' => null,
            'loggable_id' => $template->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Template'
        ];
        
        $countBefore = Template::all()->count();
        app(DeleteTemplateAction::class)->fill(['template' => $countBefore + 1])->run();
        $countAfter = Template::all()->count();

        $this->assertEquals($countBefore, $countAfter);
        $this->assertDatabaseMissing('kanban_logs', $payload);
    }
}
