<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\TaskFile;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\AWSStorage\S3Storage;
use Xguard\LaravelKanban\Models\Task;

class DeleteTaskFilesAction extends Action
{
    const TASK = 'task';
    const FILES_IDS = 'filesIds';

    public function rules(): array
    {
        return [
            self::TASK => ['required', 'instance_of:' . Task::class],
            self::FILES_IDS => ['required', 'array']
        ];
    }

    public function handle()
    {
        $disk = app(S3Storage::class);
        $filesToDelete = TaskFile::whereIn(TaskFile::ID, $this->filesIds)->get();

        foreach ($filesToDelete as $file) {
            $disk->delete($file->task_file_url);
            $file->delete();

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_FILE_REMOVED,
                'Deleted file  [' . $file->task_file_url . ']',
                null,
                $file->task_id,
                LoggableTypes::TASK()->getValue()
            );
        }
    }
}
