<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\SharedTaskData;

class UpdateTaskDescriptionAction extends Action
{
    public function authorize()
    {
        return AccessManager::canAccessBoardUsingTaskId($this->taskId);
    }
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'checkBoxContent' => ['required', 'string'],
            'description' => ['required', 'string'],
            'isChecked' => ['required', 'boolean'],
            'taskId' => ['required', 'integer', 'gt:0'],
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();
            $task = Task::find($this->taskId);
            SharedTaskData::where('id', $task->shared_task_data_id)->update(['description' => $this->description]);

            if ($this->isChecked) {
                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_CHECKLIST_ITEM_CHECKED,
                    $this->checkboxContent,
                    null,
                    $task->id,
                    'Xguard\LaravelKanban\Models\Task'
                );
            } else {
                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_CHECKLIST_ITEM_UNCHECKED,
                    $this->checkboxContent,
                    null,
                    $task->id,
                    'Xguard\LaravelKanban\Models\Task'
                );
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
