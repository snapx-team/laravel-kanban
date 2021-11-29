<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Models\Task;

class SyncAssignedEmployeesToTaskAction extends Action
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
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->assignedTo !== null) {
            $employeeArray = [];
            foreach ($this->assignedTo as $employee) {
                array_push($employeeArray, $employee['employee']['id']);
            }

            $assignedToResponse = $this->task->assignedTo()->sync($employeeArray);

            foreach ($assignedToResponse['attached'] as $employeeId) {
                $employee = Employee::find($employeeId);
                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_ASSIGNED_TO_USER,
                    'User ' . $employee->user->full_name . ' has been assigned to task [' . $this->task->task_simple_name . ']',
                    $employee->id,
                    $this->task->id,
                    'Xguard\LaravelKanban\Models\Task'
                );
            }

            foreach ($assignedToResponse['detached'] as $employeeId) {
                $employee = Employee::find($employeeId);
                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_UNASSIGNED_TO_USER,
                    'User ' . $employee->user->full_name . ' has been unassigned from task [' . $this->task->task_simple_name . ']',
                    $employee->id,
                    $this->task->id,
                    'Xguard\LaravelKanban\Models\Task'
                );
            }
        }
    }
}
