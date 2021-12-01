<?php

namespace Tests\Unit;

use App\Models\User;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Metrics\GetBadgeDataAction;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;

class GetBadgeDataActionTest extends TestCase
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

    public function testGetTaskCountByBadgesAccuratelyBetweenSelectedDates()
    {
        $badge1 = factory(Badge::class)->create(['name' => 'badge1']);
        $badge2 = factory(Badge::class)->create(['name' => 'badge2']);
        $badge3 = factory(Badge::class)->create(['name' => 'badge3']);

        factory(Task::class, 5)->create(['badge_id' => $badge1->id]);
        factory(Task::class, 10)->create(['badge_id' => $badge2->id]);
        factory(Task::class)->create(['badge_id' => $badge3->id, 'created_at' => new DateTime('2 days ago')]); //before selected dates

        $end = new DateTime('now');
        $start = new DateTime('yesterday');

        $data = app(GetBadgeDataAction::class)->fill(['start' => $start->format('Y-m-d H:i:s'), 'end' => $end->format('Y-m-d H:i:s'), ])->run();
        $this->assertCount(2, $data['names']); //assert only 2 because the third is outside selected date
        $this->assertCount(2, $data['hits']); //assert only 2 because the third is outside selected date
        $this->assertEquals(5, $data['hits'][0]);
        $this->assertEquals(10, $data['hits'][1]);
    }
}
