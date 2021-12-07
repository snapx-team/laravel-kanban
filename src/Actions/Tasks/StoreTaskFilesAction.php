<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\AWSStorage\S3Storage;
use Xguard\LaravelKanban\Models\Task;

class StoreTaskFilesAction extends Action
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
            'filesToUpload' => ['required'],
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $disk = app(S3Storage::class);

        foreach ($this->filesToUpload as $file) {
            if ($file != null) {
                $path = 'task_files/' . $this->task->id . '/' . \Str::random(40) . '/' . $file->getClientOriginalName();
                $disk->put($path, file_get_contents($file));
                $this->task->taskFiles()->create([
                    'task_file_url' => $path,
                ]);
            }
        }
    }
}
