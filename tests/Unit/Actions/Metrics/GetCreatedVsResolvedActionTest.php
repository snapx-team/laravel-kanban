<?php

namespace Tests\Unit;

use App\Models\User;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Metrics\GetCreatedVsResolvedAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

class GetCreatedVsResolvedActionTest extends TestCase
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

    public function testGetCreatedVsResolvedData()
    {
        $task1 = factory(Task::class)->create();
        $task2 = factory(Task::class)->create();
        $task3 = factory(Task::class)->create();
        $task4 = factory(Task::class)->create();

        $tasks = [$task1, $task2, $task3];

        $end = new DateTime('now');
        $start = new DateTime('3 days ago');
        $oneDays = new DateTime('1 days ago');
        $twoDays = new DateTime('2 days ago');


        factory(Log::class)->create([
            'user_id' => factory(User::class),
            'log_type' => LOG::TYPE_CARD_CREATED,
            'targeted_employee_id' => factory(Employee::class),
            'loggable_id' => $task4->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task',
            'created_at' => $twoDays->format('Y-m-d H:i:s')
        ]);

        foreach ($tasks as $task) {
            factory(Log::class)->create([
                'user_id' => factory(User::class),
                'log_type' => LOG::TYPE_CARD_CREATED,
                'targeted_employee_id' => factory(Employee::class),
                'loggable_id' => $task->id,
                'loggable_type' => 'Xguard\LaravelKanban\Models\Task',
                'created_at' => $oneDays->format('Y-m-d H:i:s')
            ]);

            factory(Log::class)->create([
                'user_id' => factory(User::class),
                'log_type' => LOG::TYPE_CARD_COMPLETED,
                'targeted_employee_id' => factory(Employee::class),
                'loggable_id' => $task->id,
                'loggable_type' => 'Xguard\LaravelKanban\Models\Task',
                'created_at' => $oneDays->format('Y-m-d H:i:s')
            ]);
        }

        $data = app(GetCreatedVsResolvedAction::class)->fill(['start' => $start->format('Y-m-d H:i:s'), 'end' => $end->format('Y-m-d H:i:s'),])->run();

        $this->assertCount(3, $data['series'][0]->data); // number of days selected

        $this->assertEquals(0, $data['series'][0]->data[0]); // zero created 3 days ago
        $this->assertEquals(1, $data['series'][0]->data[1]); // 1 created before yesterday
        $this->assertEquals(3, $data['series'][0]->data[2]); // 3 created yesterday


        $this->assertEquals(0, $data['series'][1]->data[0]); // zero created 3 days ago
        $this->assertEquals(0, $data['series'][1]->data[1]); // zero resolved before yesterday
        $this->assertEquals(3, $data['series'][1]->data[2]); // 3 resolved yesterday
    }
}
