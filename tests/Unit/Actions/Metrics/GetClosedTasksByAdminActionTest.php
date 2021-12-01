<?php

namespace Tests\Unit;

use App\Models\User;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Metrics\GetClosedTasksByAdminAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

class GetClosedTasksByAdminActionTest extends TestCase
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

    public function testGetClosedTasksByAdminBetweenSelectedDates()
    {
        $admin = factory(Employee::class)->states('admin')->create();

        $end = new DateTime('now');
        $start = new DateTime('yesterday');
        $twoDaysAgo = new DateTime('2 days ago');

        factory(Log::class, 10)->create([
            'user_id' => $admin,
            'log_type' => LOG::TYPE_CARD_COMPLETED,
            'loggable_id' => factory(Task::class),
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task',
        ]);

        factory(Log::class, 10)->create([
            'user_id' => $admin,
            'log_type' => LOG::TYPE_CARD_COMPLETED,
            'loggable_id' => factory(Task::class),
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task',
            'created_at' => $twoDaysAgo->format('Y-m-d H:i:s')
        ]);

        $data = app(GetClosedTasksByAdminAction::class)->fill(['start' => $start->format('Y-m-d H:i:s'), 'end' => $end->format('Y-m-d H:i:s'),])->run();
        $this->assertCount(1, $data['names']); //assert only 1 because we only want tasks closed by admins
        $this->assertCount(1, $data['hits']); //assert only 1 because we only want tasks closed by admins
        $this->assertEquals(10, $data['hits'][0]);
    }
}
