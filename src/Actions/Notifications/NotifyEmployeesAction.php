<?php

namespace Xguard\LaravelKanban\Actions\Notifications;

use Xguard\LaravelKanban\Models\EmployeeBoardNotificationSetting;
use Xguard\LaravelKanban\Models\Log;
use Lorisleiva\Actions\Action;

class NotifyEmployeesAction extends Action
{

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'log' => ['required', 'instance_of:' . Log::class],
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return void
     */

    public function handle()
    {
        if ($this->log->loggable_type == 'Xguard\LaravelKanban\Models\Task') {
            //notify reporter unless reporter performed the action
            if (intval(session('employee_id')) !== intval($this->log->loggable->reporter_id)) {
                $this->attachNotificationIfAllowed($this->log, $this->log->loggable->board->id, $this->log->loggable->reporter->id);
            }
            //notify assigned-to users
            foreach ($this->log->loggable->assignedTo as $employee) {
                $this->attachNotificationIfAllowed($this->log, $this->log->loggable->board->id, $employee->id);
            }
            //notify targeted employee
            if ($this->log->targeted_employee_id !== null) {
                $this->attachNotificationIfAllowed($this->log, $this->log->loggable->board->id, $this->log->targeted_employee_id);
            }
        }

        if ($this->log->loggable_type == 'Xguard\LaravelKanban\Models\Comment') {
            //notify reporter unless reporter performed the action

            if (intval(session('employee_id')) !== intval($this->log->loggable->task->reporter_id)) {
                $this->attachNotificationIfAllowed($this->log, $this->log->loggable->task->board->id, $this->log->loggable->task->reporter->id);
            }
            //notify assigned-to users
            foreach ($this->log->loggable->task->assignedTo as $employee) {
                $this->attachNotificationIfAllowed($this->log, $this->log->loggable->task->board->id, $employee->id);
            }
            //notify targeted employee
            if ($this->log->targeted_employee_id !== null) {
                $this->attachNotificationIfAllowed($this->log, $this->log->loggable->task->board->id, $this->log->targeted_employee_id);
            }
        }

        if ($this->log->loggable_type == 'Xguard\LaravelKanban\Models\Board') {
            //notify targeted employee (added/deleted members)
            if ($this->log->targeted_employee_id !== null) {
                $this->attachNotificationIfAllowed($this->log, $this->log->loggable->id, $this->log->targeted_employee_id);
            }
        }
    }

    /**
     * Attaches notification if user hasn't disallowed the notification type for a given board
     *
     */

    public function attachNotificationIfAllowed($log, $boardId, $employeeId)
    {
        $employeeBoardNotificationSettings = EmployeeBoardNotificationSetting::where('board_id', $boardId)
            ->where('employee_id', $employeeId)->first();

        if (!$employeeBoardNotificationSettings) {
            $log->notifications()->attach($employeeId);
        } else {
            if (!in_array($log->log_type, $employeeBoardNotificationSettings->getUnserializedOptionsAttribute())) {
                $log->notifications()->attach($employeeId);
            }
        }
    }
}
