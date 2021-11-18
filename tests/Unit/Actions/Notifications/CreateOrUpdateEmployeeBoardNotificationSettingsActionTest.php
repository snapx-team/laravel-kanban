<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Notifications\CreateOrUpdateEmployeeBoardNotificationSettingsAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\EmployeeBoardNotificationSetting;

class CreateOrUpdateEmployeeBoardNotificationSettingsActionTest extends TestCase
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

    public function testAUserCanCreateNotificationSettingsForMultipleBoards()
    {

        $board1 = factory(Board::class)->create();
        $board2 = factory(Board::class)->create();
        $board3 = factory(Board::class)->create();

        $notificationSettings = array(
            $board1->id => array('TASK_STATUS_GROUP', 'TASK_MOVEMENT_GROUP'),
            $board2->id => array('TASK_STATUS_GROUP', 'TASK_MOVEMENT_GROUP'),
            $board3->id => array('TASK_STATUS_GROUP', 'TASK_MOVEMENT_GROUP'),
        );

        (new CreateOrUpdateEmployeeBoardNotificationSettingsAction(['notificationSettings' => $notificationSettings   ]))->run();

        $this->assertCount(3, EmployeeBoardNotificationSetting::all());
    }

}
