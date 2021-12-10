<?php

namespace Tests\Unit\Actions\Tasks;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Prophecy\PhpUnit\ProphecyTrait;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Tasks\DeleteTaskFilesAction;
use Xguard\LaravelKanban\Actions\Tasks\StoreTaskFilesAction;
use Xguard\LaravelKanban\Actions\Tasks\UpdateTaskFilesAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\TaskFile;

class UpdateTaskFilesActionTest extends TestCase
{

    use WithFaker, RefreshDatabase, ProphecyTrait;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        Auth::setUser($user);

        $employee = factory(Employee::class)->states('admin')->create(['user_id' => $user->id,]);
        session(['role' => 'admin', 'employee_id' => $employee->id]);
    }

    public function testStoreTaskFilesActionsIsCalled()
    {

        $task = factory(Task::class)->create();
        $file = UploadedFile::fake()->image('photo1.jpg');

        $storeTaskFilesAction = $this->prophesize(StoreTaskFilesAction::class);
        $this->instance(StoreTaskFilesAction::class, $storeTaskFilesAction->reveal());

        $storeTaskFilesAction->fill([
            'task' => $task, 'filesToUpload' => [$file]
        ])->shouldBeCalledTimes(1)->willReturn($storeTaskFilesAction);
        $storeTaskFilesAction->run()->shouldBeCalledTimes(1);

        app(UpdateTaskFilesAction::class)->fill([
            'task' => $task, 'taskFiles' => [], 'filesToUpload' => [$file]
        ])->run();
    }

    public function testADeleteAndCreateTaskFilesActionsAreCalled()
    {
        $task = factory(Task::class)->create();

        $taskFile1 = factory(TaskFile::class)->create(['task_id' => $task->id, 'task_file_url' => '/test1']);
        $taskFile2 = factory(TaskFile::class)->create(['task_id' => $task->id, 'task_file_url' => '/test2']);

        $deleteTaskFilesAction = $this->prophesize(DeleteTaskFilesAction::class);
        $this->instance(DeleteTaskFilesAction::class, $deleteTaskFilesAction->reveal());
        $deleteTaskFilesAction->fill([
            'task' => $task, 'filesIds' => [$taskFile1->id]
        ])->shouldBeCalledTimes(1)->willReturn($deleteTaskFilesAction);
        $deleteTaskFilesAction->run()->shouldBeCalledTimes(1);

        app(UpdateTaskFilesAction::class)->fill([
            'task' => $task, 'taskFiles' => [$taskFile2], 'filesToUpload' => []
        ])->run();
    }
}
