<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Task;

class UpdateTaskFilesAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'task' => ['required', 'instance_of:'.Task::class],
            'filesToUpload' => ['nullable', 'array'],
            'taskFiles' => ['nullable', 'array'],
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $currentExistingFiles = $this->task->taskFiles()->pluck('id')->toArray();
        $newExistingFiles = $this->taskFiles!= null ? array_column($this->taskFiles, 'id') : [];
        $idsOfFilesToDelete = array_diff($currentExistingFiles, $newExistingFiles);

        if (count($idsOfFilesToDelete) > 0) {
            app(DeleteTaskFilesAction::class)->fill(['task' => $this->task, 'filesIds' => $idsOfFilesToDelete])->run();
        }

        if ($this->filesToUpload && count($this->filesToUpload) > 0) {
            app(StoreTaskFilesAction::class)->fill(['task' => $this->task, 'filesToUpload' => $this->filesToUpload])->run();
        }
    }
}
