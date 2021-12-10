<?php

namespace Tests\Unit\Actions\Tasks;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Tasks\DeleteTaskFilesAction;
use Xguard\LaravelKanban\Actions\Tasks\StoreTaskFilesAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\TaskFile;

class StoreTaskFilesActionTest extends TestCase
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

    public function testAUserCanStore()
    {
        Storage::fake('kanyeban-local');
        $task= factory(Task::class)->create();
        $this->assertCount(0, TaskFile::all());
        app(StoreTaskFilesAction::class)->fill(['task' => $task, 'filesToUpload' => [UploadedFile::fake()->image('photo1.jpg')]])->run();

        $this->assertCount(1, TaskFile::all());

        foreach ($task->taskFiles as $taskFile) {
            Storage::disk('kanyeban-local')->assertExists($taskFile->task_file_url);
        }
    }
}
