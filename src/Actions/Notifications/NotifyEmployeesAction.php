<?php

namespace Xguard\LaravelKanban\Actions\Notifications;

use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Enums\SessionVariables;
use Xguard\LaravelKanban\Models\EmployeeBoardNotificationSetting;
use Xguard\LaravelKanban\Models\Log;
use Lorisleiva\Actions\Action;

class NotifyEmployeesAction extends Action
{
    const LOG = 'log';

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            self::LOG => ['required', 'instance_of:'.Log::class],
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return void
     */

    public function handle()
    {
        $globalIgnoreLogTypes = [LOG::TYPE_CARD_CHANGED_INDEX];

        if (!in_array($this->log->log_type, $globalIgnoreLogTypes)) {
            if ($this->log->loggable_type == LoggableTypes::TASK()->getValue()) {
                //notify reporter unless reporter performed the action
                if (intval(session(SessionVariables::EMPLOYEE_ID()->getValue())) !== intval($this->log->loggable->reporter_id)) {
                    $this->attachNotificationIfAllowed(
                        $this->log,
                        $this->log->loggable->board->id,
                        $this->log->loggable->reporter->id
                    );
                }
                //notify assigned-to users
                foreach ($this->log->loggable->assignedTo as $employee) {
                    $this->attachNotificationIfAllowed($this->log, $this->log->loggable->board->id, $employee->id);
                }
                //notify targeted employee
                if ($this->log->targeted_employee_id !== null) {
                    $this->attachNotificationIfAllowed(
                        $this->log,
                        $this->log->loggable->board->id,
                        $this->log->targeted_employee_id
                    );
                }
            }

            if ($this->log->loggable_type == LoggableTypes::COMMENT()->getValue()) {
                //notify reporter unless reporter performed the action
                if (intval(session(SessionVariables::EMPLOYEE_ID()->getValue())) !== intval($this->log->loggable->task->reporter_id)) {
                    $this->attachNotificationIfAllowed(
                        $this->log,
                        $this->log->loggable->task->board->id,
                        $this->log->loggable->task->reporter->id
                    );
                }
                //notify assigned-to users
                foreach ($this->log->loggable->task->assignedTo as $employee) {
                    $this->attachNotificationIfAllowed(
                        $this->log,
                        $this->log->loggable->task->board->id,
                        $employee->id
                    );
                }
                //notify targeted employee
                if ($this->log->targeted_employee_id !== null) {
                    $this->attachNotificationIfAllowed(
                        $this->log,
                        $this->log->loggable->task->board->id,
                        $this->log->targeted_employee_id
                    );
                }
            }

            if ($this->log->loggable_type == LoggableTypes::BOARD()->getValue()) {
                //notify targeted employee (added/deleted members)
                if ($this->log->targeted_employee_id !== null) {
                    $this->attachNotificationIfAllowed(
                        $this->log,
                        $this->log->loggable->id,
                        $this->log->targeted_employee_id
                    );
                }
            }
        }
    }

    /**
     * Attaches notification if user hasn't disallowed the notification type for a given board
     */

    public function attachNotificationIfAllowed($log, $boardId, $employeeId)
    {
        $employeeBoardNotificationSettings = EmployeeBoardNotificationSetting::where(EmployeeBoardNotificationSetting::BOARD_ID, $boardId)
            ->where(EmployeeBoardNotificationSetting::EMPLOYEE_ID, $employeeId)->first();

        if (!$employeeBoardNotificationSettings) {
            $log->notifications()->attach($employeeId);
        } else {
            $logsToIgnore = [];
            foreach ($employeeBoardNotificationSettings->getUnserializedOptionsAttribute() as $logGroup) {
                $logGroupArray = Log::returnLogGroup($logGroup);
                $logsToIgnore = array_merge($logsToIgnore, $logGroupArray);
            }
            if (!in_array($log->log_type, $logsToIgnore)) {
                $log->notifications()->attach($employeeId);
            }
        }
    }
}
