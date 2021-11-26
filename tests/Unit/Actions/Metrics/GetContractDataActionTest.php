<?php

namespace Tests\Unit;

use App\Models\User;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Metrics\GetContractDataAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\ErpShareables;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Models\Task;

class GetContractDataActionTest extends TestCase
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

    public function testGetTasksWithContractsData()
    {
        $sharedTaskData = factory(SharedTaskData::class)->create();
        factory(ErpShareables::class)->state('contract')->create(['shared_task_data_id' =>$sharedTaskData]);
        factory(Task::class)->create(['shared_task_data_id' =>$sharedTaskData]);

        $end = new DateTime('now');
        $start = new DateTime('yesterday');

        $data = app(GetContractDataAction::class)->fill(['start' => $start->format('Y-m-d H:i:s'), 'end' => $end->format('Y-m-d H:i:s'), ])->run();
        $this->assertCount(1, $data['names']); //assert only 2 because the third is outside selected date
        $this->assertCount(1, $data['hits']); //assert only 2 because the third is outside selected date
        $this->assertEquals(1, $data['hits'][0]);
    }
}
