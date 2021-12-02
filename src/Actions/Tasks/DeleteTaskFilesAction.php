<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use App\Models\TaskFile;
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
            'fileIds' => ['required', 'array']
        ];
    }

    /**
     * Delete file associated with contract.
     *
     * @return mixed
     */
    public function handle()
    {
        $disk = app(S3Storage::class);
        $filesToDelete = TaskFile::whereIn('id', $this->fileIds)->get();

        foreach ($filesToDelete as $file) {
            $disk->delete($file->task_file_url);
            $file->delete();
        }
    }
}
