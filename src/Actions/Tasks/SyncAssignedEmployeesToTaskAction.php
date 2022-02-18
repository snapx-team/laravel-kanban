<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Models\Task;

class SyncAssignedEmployeesToTaskAction extends Action
{
    const ASSIGNED_TO = 'assignedTo';
    const TASK = 'task';

    public function rules(): array
    {
        return [
            'task' => ['required', 'instance_of:' . Task::class],
        ];
    }

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
                if ($employee) {
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_CARD_ASSIGNED_TO_USER,
                        'User '.$employee->user->full_name.' has been assigned to task ['.$this->task->task_simple_name.']',
                        $employee->id,
                        $this->task->id,
                        LoggableTypes::TASK()->getValue()
                    );
                }
            }

            foreach ($assignedToResponse['detached'] as $employeeId) {
                $employee = Employee::find($employeeId);
                if ($employee) {
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_CARD_UNASSIGNED_TO_USER,
                        'User ' . $employee->user->full_name . ' has been unassigned from task [' . $this->task->task_simple_name . ']',
                        $employee->id,
                        $this->task->id,
                        LoggableTypes::TASK()->getValue()
                    );
                }
            }
        }
    }
}
