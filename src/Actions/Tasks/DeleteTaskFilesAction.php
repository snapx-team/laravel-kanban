<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\TaskFile;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\AWSStorage\S3Storage;
use Xguard\LaravelKanban\Models\Task;

class DeleteTaskFilesAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'task' => ['required', 'instance_of:' . Task::class],
            'filesIds' => ['required', 'array']
        ];
    }

    /**
     * Delete file associated with contract.
     *
     * @return void
     */
    public function handle()
    {
        $disk = app(S3Storage::class);
        $filesToDelete = TaskFile::whereIn('id', $this->filesIds)->get();

        foreach ($filesToDelete as $file) {
            $disk->delete($file->task_file_url);
            $file->delete();

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_FILE_REMOVED,
                'Deleted file  [' . $file->task_file_url . ']',
                null,
                $file->task_id,
                'Xguard\LaravelKanban\Models\Task'
            );
        }
    }
}
