<?php

namespace Tests\Unit\Actions\Employees;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Employees\CreateOrUpdateEmployeeAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;

class CreateOrUpdateEmployeeActionTest extends TestCase
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

    public function testCreateOrUpdateEmployeeActionTest()
    {
        $users = factory(User::class, 2)->create();
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $users[0]->id]);
        app(CreateOrUpdateEmployeeAction::class)->fill([
            'selectedUsers' => [
                [
                    'id' => $users[0]->id,
                ],
                [
                    'id' => $users[1]->id,
                ]
            ],
            'role' => 'test'
        ])->run();

        $payload1 = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_EMPLOYEE_UPDATED,
            'description' => 'Updated employee [' . $employee->user->full_name . ']',
            'targeted_employee_id' => $employee->id,
            'loggable_id' => $employee->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Employee'
        ];

        $employee2 = Employee::where('user_id', $users[1]->id)->first();

        $payload2 = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_EMPLOYEE_CREATED,
            'description' => 'Added employee [' . $employee2->user->full_name . ']',
            'targeted_employee_id' => $employee2->id,
            'loggable_id' => $employee2->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Employee'
        ];

        $this->assertDatabaseHas('kanban_employees', ['user_id' => $users[0]->id]);
        $this->assertDatabaseHas('kanban_employees', ['user_id' => $users[1]->id]);
        $this->assertDatabaseHas('kanban_logs', $payload1);
        $this->assertDatabaseHas('kanban_logs', $payload2);
    }
}
