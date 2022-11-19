<?php

namespace Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class TaskRepositoryTest extends TestCase
{
    use RefreshDatabase;

    const ROLE = 'role';
    const ADMIN = 'admin';
    const EMPLOYEE_ID = 'employee_id';

    public function setUp(): void
    {
        parent::setUp();
        $this->taskRepository = new TasksRepository();
        $this->employee = factory(Employee::class)->create();
        session([self::ROLE => self::ADMIN, self::EMPLOYEE_ID => $this->employee->id]);
    }

    public function testGetLatestTaskByEmployee()
    {
        factory(Task::class)->create([Task::REPORTER_ID => session(self::EMPLOYEE_ID)]);
        $retrievedLatestTaskByEmployee = $this->taskRepository::getLatestTaskByEmployee(session(self::EMPLOYEE_ID));
        $this->assertDatabaseHas('kanban_tasks', [
            Task::ID => $retrievedLatestTaskByEmployee->id,
            Task::NAME => $retrievedLatestTaskByEmployee->name,
        ]);
    }
}
