<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Throwable;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\SharedTaskData;

class UpdateTaskDescriptionAction extends Action
{
    const CHECK_BOX_CONTENT = 'checkBoxContent';
    const DESCRIPTION = 'description';
    const IS_CHECKED = 'isChecked';
    const TASK_ID = 'taskId';

    public function authorize()
    {
        return AccessManager::canAccessBoardUsingTaskId($this->taskId);
    }

    public function rules(): array
    {
        return [
            self::CHECK_BOX_CONTENT => ['required', 'string'],
            self::DESCRIPTION => ['required', 'string'],
            self::IS_CHECKED => ['required', 'boolean'],
            self::TASK_ID => ['required', 'integer', 'gt:0'],
        ];
    }

    /**
     * @throws Throwable
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();
            $task = Task::find($this->taskId);
            SharedTaskData::where('id', $task->shared_task_data_id)->update([SharedTaskData::DESCRIPTION => $this->description]);

            if ($this->isChecked) {
                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_CHECKLIST_ITEM_CHECKED,
                    $this->checkBoxContent,
                    null,
                    $task->id,
                    LoggableTypes::TASK()->getValue()
                );
            } else {
                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_CHECKLIST_ITEM_UNCHECKED,
                    $this->checkBoxContent,
                    null,
                    $task->id,
                    LoggableTypes::TASK()->getValue()
                );
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
