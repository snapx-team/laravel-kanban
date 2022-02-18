<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\AWSStorage\S3Storage;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\TaskFile;

class StoreTaskFilesAction extends Action
{
    const TASK = 'task';
    const FILES_TO_UPLOAD = 'filesToUpload';

    public function rules(): array
    {
        return [
            self::TASK => ['required', 'instance_of:' . Task::class],
            self::FILES_TO_UPLOAD => ['required'],
        ];
    }

    public function handle()
    {
        $disk = app(S3Storage::class);

        foreach ($this->filesToUpload as $file) {
            if ($file != null) {
                $path = 'task_files/' . $this->task->id . '/' . \Str::random(40) . '/' . $file->getClientOriginalName();
                $disk->put($path, file_get_contents($file));
                $this->task->taskFiles()->create([
                    TaskFile::TASK_FILE_URL => $path,
                ]);

                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_FILE_ADDED,
                    'Added file  [' . $path  . ']',
                    null,
                    $this->task->id,
                    LoggableTypes::TASK()->getValue()
                );
            }
        }
    }
}
