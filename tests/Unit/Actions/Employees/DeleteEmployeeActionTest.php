<?php

namespace Tests\Unit\Actions\Employees;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Employees\DeleteEmployeeAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;

class DeleteEmployeeActionTest extends TestCase
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

    public function testDeletionOfEmployee()
    {
        $otherUser = factory(User::class)->create();
        $otherEmployee = factory(Employee::class)->states('admin')->create(['user_id' => $otherUser->id]);

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_EMPLOYEE_DELETED,
            'description' => 'Deleted employee ['.$otherEmployee->user->full_name.']',
            'targeted_employee_id' => $otherEmployee->id,
            'loggable_id' => $otherEmployee->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Employee'
        ];
        
        app(DeleteEmployeeAction::class)->fill(['employeeId' => $otherEmployee->id])->run();
        
        $this->assertDatabaseHas('kanban_logs', $payload);    
        $this->assertNull(Employee::where('id', $otherEmployee->id)->first());
    }

    public function testErrorIsThrownWhenAnInvalidIdIsGiven()
    {
        $this->expectException(Exception::class);
        $otherUser = factory(User::class)->create();
        $otherEmployee = factory(Employee::class)->states('admin')->create(['user_id' => $otherUser->id]);
        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_EMPLOYEE_DELETED,
            'description' => 'Deleted employee ['.$otherEmployee->user->full_name.']',
            'targeted_employee_id' => $otherEmployee->id,
            'loggable_id' => $otherEmployee->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Employee'
        ];
        
        $countBefore = Employee::all()->count();
        app(DeleteEmployeeAction::class)->fill(['employeeId' => $countBefore + 1])->run();
        $countAfter = Employee::all()->count();

        $this->assertEquals($countBefore, $countAfter);
        $this->assertDatabaseMissing('kanban_logs', $payload);
    }
}
