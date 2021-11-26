<?php

namespace Tests\Unit;

use App\Models\User;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Metrics\GetTasksCreatedByEmployeeAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;

class GetTasksCreatedByEmployeeActionTest extends TestCase
{

    use WithFaker, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        Auth::setUser($user);
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $user->id,]);
        session(['role' => 'admin', 'employee_id' => $employee->id]);
    }

    public function testGetTaskCreatedByEmployeeIsAccurate()
    {
        $end = new DateTime('today');
        $start = new DateTime('yesterday');

        $employee1 = factory(Employee::class)->create();
        $employee2 = factory(Employee::class)->create();

        factory(Task::class, 5)->create(['reporter_id' => $employee1->id]);
        factory(Task::class, 10)->create(['reporter_id' => $employee2->id]);

        $data = app(GetTasksCreatedByEmployeeAction::class)->fill(['start' => $start->format('Y-m-d H:i:s'), 'end' => $end->format('Y-m-d H:i:s'),])->run();
        $this->assertCount(2, $data['hits']);
        $this->assertCount(2, $data['names']);
        $this->assertEquals(5, $data['hits'][0]);  // asserting first employee created 5 tasks
        $this->assertEquals(10, $data['hits'][1]); // asserting second employee created 10 tasks
    }
}
