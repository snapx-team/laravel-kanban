<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Task;

class UpdateTaskFilesActions extends Action
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
            'taskFiles' => ['nullable', 'array'],
            'addedTaskFiles' => ['nullable', 'array'],
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $oldFilesIds = $this->task->taskFiles()->pluck('id')->toArray();
        $fileIds = is_null($this->taskFiles) ? [] : array_column($this->taskFiles, 'id');
        $differentIds = array_diff($oldFilesIds, $fileIds);
        
        if (count($differentIds) > 0) {
            app(DeleteTaskFilesAction::class)->fill(['task' => $this->task, 'filesIds' => $differentIds])->run();
        }

        if ($this->addedTaskFiles && count($this->addedTaskFiles) > 0 ) {
            app(StoreTaskFilesAction::class)->fill(['task' => $this->task, 'uploadedFiles' => $this->addedTaskFiles])->run();
        }
    }
}
