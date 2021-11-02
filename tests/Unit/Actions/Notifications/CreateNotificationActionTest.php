<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Notifications\NotifyEmployeesAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\EmployeeBoardNotificationSetting;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\Comment;

class CreateNotificationActionTest extends TestCase
{

    use WithFaker, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $user = factory(User::class)->create();
        Auth::setUser($user);

        Board::create(['name' => 'test board']);
        Employee::create(['user_id' => $user->id, 'role' => 'admin']);
        Task::create(['name' => 'test task', 'board_id' => 1, 'reporter_id' => 1, 'shared_task_data_id' => 1]);
        Comment::create(['task_id' => 1, 'employee_id' => 1, 'comment' => 'test']);
        session(['role' => 'admin', 'employee_id' => '1']);
    }

    public function testANotificationIsCreatedWhenLogWithNotifiableEmployeeIsCreated()
    {
        $log = Log::create([
            'user_id' => 1,
            'log_type' => Log::TYPE_COMMENT_CREATED,
            'description' => 'test',
            'targeted_employee_id' => 1,
            'loggable_id' => 1,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Comment'
        ]);

        (new NotifyEmployeesAction(['log' => $log]))->run();
        $this->assertCount(1, DB::table('kanban_employee_log')->get());
    }

    public function testANotificationIsNotCreatedIfEmployeeBoardNotificationSettingsIsSetToIgnoreTheLogType()
    {
        $arrayOfLogTypesToIgnore = [Log::TYPE_COMMENT_CREATED];
        $serializedArrayOfLogTypesToIgnore = serialize($arrayOfLogTypesToIgnore);
        EmployeeBoardNotificationSetting::create(['board_id' => 1, 'employee_id' => 1, 'ignore_types' => $serializedArrayOfLogTypesToIgnore]);

        $log = Log::create([
            'user_id' => 1,
            'log_type' => Log::TYPE_COMMENT_CREATED,
            'description' => 'test',
            'targeted_employee_id' => 1,
            'loggable_id' => 1,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Comment'
        ]);

        (new NotifyEmployeesAction(['log' => $log]))->run();
        $this->assertCount(0, DB::table('kanban_employee_log')->get());
    }
}
