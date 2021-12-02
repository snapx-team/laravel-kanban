<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use App\Context\Context;
use App\Enums\Permissions;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\AWSStorage\S3Storage;

class StoreTaskFilesAction extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @param Context $context
     * @return bool
     */
    public function authorize(Context $context)
    {
        return $context->user()->hasPermissionTo(Permissions::EDIT_JOBSITES()->getValue());
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'task' => ['required', 'instance_of:' . Task::class],
            'uploadedFiles' => ['required'],
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

        foreach ($this->uploadedFiles as $file) {
            if ($file != null) {
                $path = 'task_files/' . $this->task->id . '/task_file_url/' . \Str::random(40);
                $disk->put($path, file_get_contents($this->$file));
                $this->task->taskFiles()->create([
                    'task_file_url' => $path,
                ]);
            }
        }
    }
}
