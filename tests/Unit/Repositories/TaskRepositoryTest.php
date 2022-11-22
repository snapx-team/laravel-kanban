<?php

namespace Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Xguard\LaravelKanban\Enums\DateTimeFormats;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Repositories\TasksRepository;
use DateTime;

class TaskRepositoryTest extends TestCase
{
    use RefreshDatabase;

    const ROLE = 'role';
    const ADMIN = 'admin';
    const EMPLOYEE_ID = 'employee_id';
    const FIVE = 5;

    public function setUp(): void
    {
        parent::setUp();
        $this->taskRepository = new TasksRepository();
        $this->employee = factory(Employee::class)->create();
        session([self::ROLE => self::ADMIN, self::EMPLOYEE_ID => $this->employee->id]);
    }

    public function testGetRecentlyCreatedTasksByEmployee()
    {
        $dateTime = new DateTime('yesterday');
        factory(Task::class, self::FIVE)->create([Task::REPORTER_ID => session(self::EMPLOYEE_ID)]);
        factory(Task::class, self::FIVE)->create([Task::REPORTER_ID => session(self::EMPLOYEE_ID), Task::CREATED_AT => $dateTime->format(DateTimeFormats::DATE_TIME_FORMAT()->getValue())]);
        $retrievedRecentlyCreatedTasksByEmployee = $this->taskRepository::getRecentlyCreatedTasksByEmployee(session(self::EMPLOYEE_ID));
        $this->assertEquals($retrievedRecentlyCreatedTasksByEmployee->count(), self::FIVE);
    }
}
