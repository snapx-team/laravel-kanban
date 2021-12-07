<?php

namespace Tests\Unit\Actions\Tasks;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Tasks\CreateTaskAction;
use Xguard\LaravelKanban\Actions\Tasks\DeleteTaskFilesAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\TaskFile;

class DeleteTaskFilesActionTest extends TestCase
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

    public function testAUserCanDeleteAFile()
    {
        Storage::fake('kanyeban-local');
        $fileName = Storage::disk('kanyeban-local')->put('/', UploadedFile::fake()->image('photo1.jpg'));
        Storage::disk('kanyeban-local')->assertExists($fileName);

        $task= factory(Task::class)->create();
        $taskFile= factory(TaskFile::class)->create(['task_id' =>$task->id, 'task_file_url' => $fileName ]);
        $this->assertCount(1, TaskFile::all());

        app(DeleteTaskFilesAction::class)->fill(['task' => $task, 'filesIds' => [$taskFile->id]])->run();
        $this->assertCount(0, TaskFile::all());
        Storage::disk('kanyeban-local')->assertMissing($fileName);
    }

}
