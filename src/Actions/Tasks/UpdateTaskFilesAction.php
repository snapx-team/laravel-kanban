<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\TaskFile;

class UpdateTaskFilesAction extends Action
{
    const TASK = 'task';
    const FILES_TO_UPLOAD = 'filesToUpload';
    const TASK_FILES = 'taskFiles';

    public function rules(): array
    {
        return [
            self::TASK => ['required', 'instance_of:'.Task::class],
            self::FILES_TO_UPLOAD => ['nullable', 'array'],
            self::TASK_FILES => ['nullable', 'array'],
        ];
    }

    public function handle()
    {
        $currentExistingFiles = $this->task->taskFiles()->pluck(TaskFile::ID)->toArray();
        $newExistingFiles = $this->taskFiles!= null ? array_column($this->taskFiles, TaskFile::ID) : [];
        $idsOfFilesToDelete = array_diff($currentExistingFiles, $newExistingFiles);

        if (count($idsOfFilesToDelete) > 0) {
            app(DeleteTaskFilesAction::class)->fill([DeleteTaskFilesAction::TASK => $this->task, DeleteTaskFilesAction::FILES_IDS => $idsOfFilesToDelete])->run();
        }

        if ($this->filesToUpload && count($this->filesToUpload) > 0) {
            app(StoreTaskFilesAction::class)->fill([StoreTaskFilesAction::TASK => $this->task, StoreTaskFilesAction::FILES_TO_UPLOAD => $this->filesToUpload])->run();
        }
    }
}
