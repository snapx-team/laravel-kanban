<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\DashboardData\GetDashboardDataAction;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Template;

class GetDashboardDataActionTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        Auth::setUser($user);
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $user->id,]);
        session(['role' => 'admin', 'employee_id' => $employee->id]);

        factory(Employee::class, 5)->create();
        factory(Board::class, 10)->create();
        factory(Template::class, 15)->create(['badge_id'=>null]);
        factory(Badge::class, 20)->create();
    }

    public function testGetDashboardDataIfAdmin()
    {

        $dashboardData = app(GetDashboardDataAction::class)->run();

        $this->assertCount(6, $dashboardData['employees']);
        $this->assertCount(10, $dashboardData['boards']);
        $this->assertCount(15, $dashboardData['templates']);
        $this->assertCount(20, $dashboardData['badges']);
    }

    public function testGetDashboardDataIfEmployee()
    {
        session(['role' => 'employee', 'employee_id' => session('employee_id')]);
        factory(Member::class)->create(['employee_id' => session('employee_id')]);

        $dashboardData = app(GetDashboardDataAction::class)->run();

        $this->assertCount(6, $dashboardData['employees']);
        $this->assertCount(1, $dashboardData['boards']); //only member of 1 board
        $this->assertCount(15, $dashboardData['templates']);
        $this->assertCount(20, $dashboardData['badges']);
    }
}
