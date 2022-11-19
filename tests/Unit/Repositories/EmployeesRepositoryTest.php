<?php

namespace Tests\Unit\Repositories;

use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Repositories\EmployeesRepository;

class EmployeesRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->employeesRepository = new EmployeesRepository();
    }

    public function testUpdateLastNotificationCheck()
    {
        $employee = factory(Employee::class)->create(['last_notif_check' => null]);
        $this->assertNull($employee->last_notif_check);
        $dateTime = new DateTime('NOW');

        $this->employeesRepository::updateLastNotificationCheck($employee->user_id, $dateTime);

        $employee->refresh();

        $this->assertNotNull($employee->last_notif_check);
        $this->assertEquals($employee->last_notif_check, $dateTime->format('Y-m-d H:i:s'));
    }

    public function testGetNotificationCountReturnsCountOfAllNotifsIfLastNotifCheckIsNull()
    {
        $employee = factory(Employee::class)->create(['last_notif_check' => null]);
        $logsToShowCount = rand(1, 10);
        $logs = factory(Log::class, $logsToShowCount)->create();
        foreach ($logs as $log) {
            $log->notifications()->attach($employee->id);
        }
        $this->assertEquals($logsToShowCount, employeesRepository::getNotificationCount($employee->user_id));
    }

    public function testGetNotificationCountReturnsCountNotifsAfterLastNotifCheckIfLastNotifCheckIsNotNull()
    {
        $dateTime = new DateTime('NOW');
        $employee = factory(Employee::class)->create(['last_notif_check' => $dateTime->format('Y-m-d H:i:s')]);
        $logs = factory(Log::class, 10)->create();
        $logsToShowCount = 0;
        foreach ($logs as $log) {
            if (rand(0, 1)) {
                $logsToShowCount++;
            } else {
                $log->created_at = $log->created_at->subDays(1);
                $log->save();
                $log->refresh();
            }
            $log->notifications()->attach($employee->id);
        }
        $this->assertEquals($logsToShowCount, employeesRepository::getNotificationCount($employee->user_id));
    }
}
