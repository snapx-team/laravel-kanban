<?php

namespace Tests\Unit;

use App\Models\User;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Metrics\GetPeakHoursTasksCreatedAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;

class GetPeakHoursTasksCreatedActionTest extends TestCase
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

    public function testGetPeakHoursTasksCreatedIsAccurate()
    {
        $end = new DateTime('today 24:00 ');
        $start = new DateTime('yesterday');

        $oneHour = new DateTime('today 23:00');
        $twoHour = new DateTime('today 22:00');
        $threeHour = new DateTime('today 21:00');

        factory(Task::class, 1)->create(['created_at' => $oneHour->format('Y-m-d H:i:s')]);
        factory(Task::class, 2)->create(['created_at' => $twoHour->format('Y-m-d H:i:s')]);
        factory(Task::class, 3)->create(['created_at' => $threeHour->format('Y-m-d H:i:s')]);

        $data = app(GetPeakHoursTasksCreatedAction::class)->fill(['start' => $start->format('Y-m-d H:i:s'), 'end' => $end->format('Y-m-d H:i:s'),])->run();
        $this->assertCount(24, $data['names']); // number of hours in a day
        $this->assertEquals(3, $data['hits'][21]); // 3 task created at 21H
        $this->assertEquals(2, $data['hits'][22]); // 2 task created at 22H
        $this->assertEquals(1, $data['hits'][23]); // 1 task created at 23H
    }
}
