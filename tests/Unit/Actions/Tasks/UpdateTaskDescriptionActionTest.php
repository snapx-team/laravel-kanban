<?php

namespace Tests\Unit\Actions\Tasks;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Tasks\UpdateTaskDescriptionAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Models\Task;

class UpdateTaskDescriptionActionTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $user = factory(User::class)->create();
        Auth::setUser($user);
        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $user->id,]);
        session(['role' => 'admin', 'employee_id' => $employee->id]);
    }

    public function testUpdateTaskDescriptionActionUpdatesDescriptionCreatesItemCheckedLogs()
    {
        $sharedTaskData = factory(SharedTaskData::class)->create();
        $task = factory(Task::class)->create(['shared_task_data_id' => $sharedTaskData->id]);

        app(UpdateTaskDescriptionAction::class)->fill([
            'checkBoxContent' => 'testCheckBoxContent',
            'description' => 'testDescription',
            'isChecked' => true,
            'taskId' => $task->id,
        ])->run();

        $task->refresh();
        $sharedTaskData->refresh();

        $payloadLog = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_CARD_CHECKLIST_ITEM_CHECKED,
            'description' => 'testCheckBoxContent',
            'targeted_employee_id' => null,
            'loggable_id' => $task->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task'
        ];

        $this->assertDatabaseHas('kanban_logs', $payloadLog);
        $this->assertEquals('testDescription', $sharedTaskData->description);
    }

    public function testUpdateTaskDescriptionActionUpdatesDescriptionCreatesItemUncheckedLogs()
    {
        $sharedTaskData = factory(SharedTaskData::class)->create();
        $task = factory(Task::class)->create(['shared_task_data_id' => $sharedTaskData->id]);

        app(UpdateTaskDescriptionAction::class)->fill([
            'checkBoxContent' => 'testCheckBoxContent',
            'description' => 'testDescription',
            'isChecked' => false,
            'taskId' => $task->id,
        ])->run();

        $task->refresh();
        $sharedTaskData->refresh();

        $payloadLog = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_CARD_CHECKLIST_ITEM_UNCHECKED,
            'description' => 'testCheckBoxContent',
            'targeted_employee_id' => null,
            'loggable_id' => $task->id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Task'
        ];

        $this->assertDatabaseHas('kanban_logs', $payloadLog);
        $this->assertEquals('testDescription', $sharedTaskData->description);
    }

    public function testUpdateTaskDescriptionActionThrowsErrorOnInvalidTaskId()
    {
        $this->expectException(Exception::class);

        app(UpdateTaskDescriptionAction::class)->fill([
            'checkBoxContent' => 'test',
            'description' => 'test',
            'isChecked' => true,
            'taskId' => 1,
        ])->run();
    }

    public function testUpdateTaskDescriptionActionThrowsValidationErrorOnNullCheckBoxContent()
    {
        $this->expectException(ValidationException::class);

        app(UpdateTaskDescriptionAction::class)->fill([
            'checkBoxContent' => null,
            'description' => 'test',
            'isChecked' => true,
            'taskId' => 1,
        ])->run();
    }

    public function testUpdateTaskDescriptionActionThrowsValidationErrorOnNullDescription()
    {
        $this->expectException(ValidationException::class);

        app(UpdateTaskDescriptionAction::class)->fill([
            'checkBoxContent' => 'test',
            'description' => null,
            'isChecked' => true,
            'taskId' => 1,
        ])->run();
    }

    public function testUpdateTaskDescriptionActionThrowsValidationErrorOnNullIsChecked()
    {
        $this->expectException(ValidationException::class);

        app(UpdateTaskDescriptionAction::class)->fill([
            'checkBoxContent' => 'test',
            'description' => 'test',
            'isChecked' => null,
            'taskId' => 1,
        ])->run();
    }

    public function testUpdateTaskDescriptionActionThrowsValidationErrorOnNullTaskId()
    {
        $this->expectException(ValidationException::class);

        app(UpdateTaskDescriptionAction::class)->fill([
            'checkBoxContent' => 'test',
            'description' => 'test',
            'isChecked' => true,
            'taskId' => null,
        ])->run();
    }
}


