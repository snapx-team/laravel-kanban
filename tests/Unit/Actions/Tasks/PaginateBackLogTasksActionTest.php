<?php

namespace Tests\Unit\Actions\Tasks;

use App\Models\User;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Tasks\PaginateBackLogTasksAction;
use Xguard\LaravelKanban\Enums\DateTimeFormats;
use Xguard\LaravelKanban\Enums\Roles;
use Xguard\LaravelKanban\Enums\SessionVariables;
use Xguard\LaravelKanban\Enums\TaskStatuses;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;

class PaginateBackLogTasksActionTest extends TestCase
{

    use WithFaker, RefreshDatabase;

    const YESTERDAY = 'yesterday';
    const NOW = 'now';
    const TEST_BOARD_NAME = 'board';
    const TEST_TASK_NAME = 'task';
    const TEST_TEXT_FILTER_BOA_2 = 'boa-2';
    const TWENTY_FIVE = 25;
    const FIFTEEN = 15;
    const ONE = 1;
    const TWO = 2;
    const TEN = 10;
    const FIVE = 5;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        Auth::setUser($user);

        $employee = factory(Employee::class)->states(Roles::ADMIN()->getValue())->create([Employee::USER_ID => $user->id,]);
        session([SessionVariables::ROLE()->getValue() => Roles::ADMIN()->getValue(), SessionVariables::EMPLOYEE_ID()->getValue() => $employee->id]);
    }

    public function testAUserCanGetBacklogTasks()
    {
        factory(Task::class, self::TWENTY_FIVE)->create([Task::REPORTER_ID => session(SessionVariables::EMPLOYEE_ID()->getValue())]);
        $now = new DateTime(self::NOW);

        $retrievedTasks = app(PaginateBackLogTasksAction::class)->fill([
            PaginateBackLogTasksAction::FILTER_START => $this->faker->dateTime(self::YESTERDAY)->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_END => $now->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_STATUS => [TaskStatuses::ACTIVE()->getValue(), TaskStatuses::COMPLETED()->getValue(), TaskStatuses::CANCELLED()->getValue()],
            PaginateBackLogTasksAction::FILTER_PLACED_IN_BOARD => true,
            PaginateBackLogTasksAction::FILTER_NOT_PLACED_IN_BOARD => true,
            PaginateBackLogTasksAction::FILTER_TEXT => null,
            PaginateBackLogTasksAction::FILTER_BADGE => [],
            PaginateBackLogTasksAction::FILTER_BOARD => [],
            PaginateBackLogTasksAction::FILTER_ASSIGNED_TO => [],
            PaginateBackLogTasksAction::FILTER_REPORTER => [],
            PaginateBackLogTasksAction::FILTER_ERP_EMPLOYEE => [],
            PaginateBackLogTasksAction::FILTER_ERP_CONTRACT => [],
        ])->run();

        $this->assertCount(self::FIFTEEN, $retrievedTasks);
    }

    public function testRetrieveTaskBasedOnStatus()
    {
        factory(Task::class, self::ONE)->create([Task::REPORTER_ID => session(SessionVariables::EMPLOYEE_ID()->getValue()), Task::STATUS => TaskStatuses::ACTIVE()->getValue()]);
        factory(Task::class, self::TWO)->create([Task::REPORTER_ID => session(SessionVariables::EMPLOYEE_ID()->getValue()), Task::STATUS => TaskStatuses::CANCELLED()->getValue()]);

        $now = new DateTime(self::NOW);
        $yesterday = new DateTime(self::YESTERDAY);

        $retrievedTasks = app(PaginateBackLogTasksAction::class)->fill([
            PaginateBackLogTasksAction::FILTER_START => $yesterday->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_END => $now->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_STATUS => [TaskStatuses::ACTIVE()->getValue()],
            PaginateBackLogTasksAction::FILTER_PLACED_IN_BOARD => true,
            PaginateBackLogTasksAction::FILTER_NOT_PLACED_IN_BOARD => true,
            PaginateBackLogTasksAction::FILTER_TEXT => null,
            PaginateBackLogTasksAction::FILTER_BADGE => [],
            PaginateBackLogTasksAction::FILTER_BOARD => [],
            PaginateBackLogTasksAction::FILTER_ASSIGNED_TO => [],
            PaginateBackLogTasksAction::FILTER_REPORTER => [],
            PaginateBackLogTasksAction::FILTER_ERP_EMPLOYEE => [],
            PaginateBackLogTasksAction::FILTER_ERP_CONTRACT => [],
        ])->run();

        $this->assertCount(self::ONE, $retrievedTasks);
    }

    public function testIgnorePlacementIfTaskStatusIncludeCompletedOrCancelled()
    {
        factory(Task::class, self::TEN)->create([Task::REPORTER_ID => session(SessionVariables::EMPLOYEE_ID()->getValue()), Task::STATUS => TaskStatuses::CANCELLED()->getValue()]);

        $now = new DateTime(self::NOW);
        $yesterday = new DateTime(self::YESTERDAY);

        $retrievedTasks = app(PaginateBackLogTasksAction::class)->fill([
            PaginateBackLogTasksAction::FILTER_START => $yesterday->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_END => $now->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_STATUS => [TaskStatuses::COMPLETED()->getValue(), TaskStatuses::CANCELLED()->getValue()],
            PaginateBackLogTasksAction::FILTER_PLACED_IN_BOARD => false,
            PaginateBackLogTasksAction::FILTER_NOT_PLACED_IN_BOARD => false,
            PaginateBackLogTasksAction::FILTER_TEXT => null,
            PaginateBackLogTasksAction::FILTER_BADGE => [],
            PaginateBackLogTasksAction::FILTER_BOARD => [],
            PaginateBackLogTasksAction::FILTER_ASSIGNED_TO => [],
            PaginateBackLogTasksAction::FILTER_REPORTER => [],
            PaginateBackLogTasksAction::FILTER_ERP_EMPLOYEE => [],
            PaginateBackLogTasksAction::FILTER_ERP_CONTRACT => [],
        ])->run();

        $this->assertCount(self::TEN, $retrievedTasks);
    }

    public function testRetrieveTaskBasedOnPlacedInBoard()
    {
        $column = factory(Column::class)->create();

        factory(Task::class, self::FIVE)->create([Task::REPORTER_ID => session(SessionVariables::EMPLOYEE_ID()->getValue()), Task::COLUMN_ID => $column->id]);
        factory(Task::class, self::TEN)->create([Task::REPORTER_ID => session(SessionVariables::EMPLOYEE_ID()->getValue()), Task::COLUMN_ID => null]);

        $now = new DateTime(self::NOW);
        $yesterday = new DateTime(self::YESTERDAY);

        $retrievedTasks = app(PaginateBackLogTasksAction::class)->fill([
            PaginateBackLogTasksAction::FILTER_START => $yesterday->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_END => $now->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_STATUS => [TaskStatuses::ACTIVE()->getValue()],
            PaginateBackLogTasksAction::FILTER_PLACED_IN_BOARD => true,
            PaginateBackLogTasksAction::FILTER_NOT_PLACED_IN_BOARD => false,
            PaginateBackLogTasksAction::FILTER_TEXT => null,
            PaginateBackLogTasksAction::FILTER_BADGE => [],
            PaginateBackLogTasksAction::FILTER_BOARD => [],
            PaginateBackLogTasksAction::FILTER_ASSIGNED_TO => [],
            PaginateBackLogTasksAction::FILTER_REPORTER => [],
            PaginateBackLogTasksAction::FILTER_ERP_EMPLOYEE => [],
            PaginateBackLogTasksAction::FILTER_ERP_CONTRACT => [],
        ])->run();

        $this->assertCount(self::FIVE, $retrievedTasks);

        $retrievedTasks = app(PaginateBackLogTasksAction::class)->fill([
            PaginateBackLogTasksAction::FILTER_START => $this->faker->dateTime(self::YESTERDAY)->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_END => $now->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_STATUS => [TaskStatuses::ACTIVE()->getValue()],
            PaginateBackLogTasksAction::FILTER_PLACED_IN_BOARD => false,
            PaginateBackLogTasksAction::FILTER_NOT_PLACED_IN_BOARD => true,
            PaginateBackLogTasksAction::FILTER_TEXT => null,
            PaginateBackLogTasksAction::FILTER_BADGE => [],
            PaginateBackLogTasksAction::FILTER_BOARD => [],
            PaginateBackLogTasksAction::FILTER_ASSIGNED_TO => [],
            PaginateBackLogTasksAction::FILTER_REPORTER => [],
            PaginateBackLogTasksAction::FILTER_ERP_EMPLOYEE => [],
            PaginateBackLogTasksAction::FILTER_ERP_CONTRACT => [],
        ])->run();

        $this->assertCount(self::TEN, $retrievedTasks);
    }

    public function testRetrieveTaskBasedOnText()
    {
        $board = factory(Board::class)->create([Board::NAME => self::TEST_BOARD_NAME]);
        $task1 = factory(Task::class)->create([Task::REPORTER_ID => session(SessionVariables::EMPLOYEE_ID()->getValue()), Task::NAME => self::TEST_TASK_NAME]);
        $task2 = factory(Task::class)->create([Task::REPORTER_ID => session(SessionVariables::EMPLOYEE_ID()->getValue()), Task::BOARD_ID => $board->id]);

        $now = new DateTime(self::NOW);
        $yesterday = new DateTime(self::YESTERDAY);

        //match task name
        $retrievedTasks1 = app(PaginateBackLogTasksAction::class)->fill([
            PaginateBackLogTasksAction::FILTER_START => $yesterday->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_END => $now->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_STATUS => [TaskStatuses::ACTIVE()->getValue()],
            PaginateBackLogTasksAction::FILTER_PLACED_IN_BOARD => true,
            PaginateBackLogTasksAction::FILTER_NOT_PLACED_IN_BOARD => true,
            PaginateBackLogTasksAction::FILTER_TEXT => $task1->name,
            PaginateBackLogTasksAction::FILTER_BADGE => [],
            PaginateBackLogTasksAction::FILTER_BOARD => [],
            PaginateBackLogTasksAction::FILTER_ASSIGNED_TO => [],
            PaginateBackLogTasksAction::FILTER_REPORTER => [],
            PaginateBackLogTasksAction::FILTER_ERP_EMPLOYEE => [],
            PaginateBackLogTasksAction::FILTER_ERP_CONTRACT => [],
        ])->run();

        //match board name
        $retrievedTasks2 = app(PaginateBackLogTasksAction::class)->fill([
            PaginateBackLogTasksAction::FILTER_START => $yesterday->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_END => $now->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_STATUS => [TaskStatuses::ACTIVE()->getValue()],
            PaginateBackLogTasksAction::FILTER_PLACED_IN_BOARD => true,
            PaginateBackLogTasksAction::FILTER_NOT_PLACED_IN_BOARD => true,
            PaginateBackLogTasksAction::FILTER_TEXT => $task2->board->name,
            PaginateBackLogTasksAction::FILTER_BADGE => [],
            PaginateBackLogTasksAction::FILTER_BOARD => [],
            PaginateBackLogTasksAction::FILTER_ASSIGNED_TO => [],
            PaginateBackLogTasksAction::FILTER_REPORTER => [],
            PaginateBackLogTasksAction::FILTER_ERP_EMPLOYEE => [],
            PaginateBackLogTasksAction::FILTER_ERP_CONTRACT => [],
        ])->run();

        //match id
        $retrievedTasks3 = app(PaginateBackLogTasksAction::class)->fill([
            PaginateBackLogTasksAction::FILTER_START => $yesterday->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_END => $now->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_STATUS => [TaskStatuses::ACTIVE()->getValue()],
            PaginateBackLogTasksAction::FILTER_PLACED_IN_BOARD => true,
            PaginateBackLogTasksAction::FILTER_NOT_PLACED_IN_BOARD => true,
            PaginateBackLogTasksAction::FILTER_TEXT => (String)$task2->id,
            PaginateBackLogTasksAction::FILTER_BADGE => [],
            PaginateBackLogTasksAction::FILTER_BOARD => [],
            PaginateBackLogTasksAction::FILTER_ASSIGNED_TO => [],
            PaginateBackLogTasksAction::FILTER_REPORTER => [],
            PaginateBackLogTasksAction::FILTER_ERP_EMPLOYEE => [],
            PaginateBackLogTasksAction::FILTER_ERP_CONTRACT => [],
        ])->run();

        //match simple task name
        $retrievedTasks4 = app(PaginateBackLogTasksAction::class)->fill([
            PaginateBackLogTasksAction::FILTER_START => $yesterday->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_END => $now->format(DateTimeFormats::DATE_TIME_FORMAT),
            PaginateBackLogTasksAction::FILTER_STATUS => [TaskStatuses::ACTIVE()->getValue()],
            PaginateBackLogTasksAction::FILTER_PLACED_IN_BOARD => true,
            PaginateBackLogTasksAction::FILTER_NOT_PLACED_IN_BOARD => true,
            PaginateBackLogTasksAction::FILTER_TEXT => self::TEST_TEXT_FILTER_BOA_2,
            PaginateBackLogTasksAction::FILTER_BADGE => [],
            PaginateBackLogTasksAction::FILTER_BOARD => [],
            PaginateBackLogTasksAction::FILTER_ASSIGNED_TO => [],
            PaginateBackLogTasksAction::FILTER_REPORTER => [],
            PaginateBackLogTasksAction::FILTER_ERP_EMPLOYEE => [],
            PaginateBackLogTasksAction::FILTER_ERP_CONTRACT => [],
        ])->run();
        $this->assertCount(self::ONE, $retrievedTasks1);
        $this->assertCount(self::ONE, $retrievedTasks2);
        $this->assertCount(self::ONE, $retrievedTasks3);
        $this->assertCount(self::ONE, $retrievedTasks4);
    }
}
