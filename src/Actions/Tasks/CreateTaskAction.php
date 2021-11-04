<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Actions\Badges\CreateBadgeAction;
use Xguard\LaravelKanban\Actions\ErpShareables\CreateErpShareablesAction;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class CreateTaskAction extends Action
{
    public function authorize()
    {
        foreach ($this->selectedKanbans as $kanban) {
            if (!AccessManager::canAccessBoard($kanban['id'])) {
                return false;
            }
        }
        return true;
    }
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'assignedTo' => ['nullable', 'array'],
            'associatedTask' => ['nullable', 'array'],
            'badge' => ['present', 'array'],
            'columnId' => ['nullable', 'integer','gt:0'],
            'deadline' => ['required', 'string'],
            'description' => ['required', 'string'],
            'erpContracts' => ['present', 'array'],
            'erpEmployees' => ['present', 'array'],
            'name' => ['required', 'string'],
            'rowId' => ['nullable', 'integer','gt:0'],
            'selectedKanbans' => ['required', 'array', "min:1"]
        ];
    }

    public function messages()
    {
        return [
            'selectedKanbans.min' => 'You need to select at least one board',
            'name.required' => 'Name is required',
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
            $badgeName = count($this->badge) > 0 ? trim($this->badge['name']) : '--';
            $badge = Badge::where('name', $badgeName)->first();

            if (!$badge) {
                $badge = app(CreateBadgeAction::class)->fill(["name" => $badgeName])->run();
            }

            if ($this->associatedTask !== null) {
                $sharedTaskDataId = $this->associatedTask['shared_task_data_id'];
            } else {
                $sharedTaskDataId = (app(CreateErpShareablesAction::class)->fill([
                    "description" => $this->description,
                    "erpEmployees" => $this->erpEmployees,
                    "erpContracts" => $this->erpContracts,
                    ])->run())->id;
            }

            $maxIndex = $this->columnId ? Task::where('column_id', $this->columnId)->where('status', 'active')->max('index') + 1 : null;

            foreach ($this->selectedKanbans as $kanban) {
                $task = Task::create([
                    'index' => $maxIndex,
                    'reporter_id' => session('employee_id'),
                    'name' => $this->name,
                    'deadline' => date('y-m-d h:m', strtotime($this->deadline)),
                    'badge_id' => $badge->id,
                    'column_id' => $this->columnId ? $this->columnId : null,
                    'row_id' => $this->rowId ? $this->rowId : null,
                    'board_id' => $kanban['id'],
                    'shared_task_data_id' => $sharedTaskDataId
                ]);

                app(SyncAssignedEmployeesToTaskAction::class)->fill(["assignedTo" => $this->assignedTo, "task"=> $task])->run();

                $log = Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_CREATED,
                    'Created new task [' . $task->task_simple_name . '] in board [' . $task->board->name . ']',
                    null,
                    $task->id,
                    'Xguard\LaravelKanban\Models\Task'
                );

                TasksRepository::versionTask([
                    "index" => $task->index,
                    "name" => $task->name,
                    "deadline" => date('y-m-d h:m', strtotime($task->deadline)),
                    "shared_task_data_id" => $task->shared_task_data_id,
                    "reporter_id" => $task->reporter_id,
                    "column_id" => $task->column_id,
                    "row_id" => $task->row_id,
                    "board_id" => $task->board_id,
                    "badge_id" => $task->badge_id,
                    "status" => $task->status ? $task->status : 'active',
                    "task_id" => $task->id,
                    "log_id" => $log->id
                ]);
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
