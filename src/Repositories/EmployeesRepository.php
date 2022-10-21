<?php

namespace Xguard\LaravelKanban\Repositories;

use Illuminate\Support\Facades\DB;
use Xguard\LaravelKanban\Models\Employee;
use DateTime;

class EmployeesRepository
{
    public static function findOrFail(int $id): Employee
    {
        return Employee::findOrFail($id);
    }

    public static function updateLastNotificationCheck(int $userId, DateTime $lastNotificationCheck): void
    {
        Employee::where('user_id', '=', $userId)->update([
            'last_notif_check' => $lastNotificationCheck
        ]);
    }

    public static function getNotificationCount(int $userId): int
    {
        $employee = Employee::where(Employee::USER_ID, '=', $userId)->first();
        if ($employee === null) {
            return 0;
        }

        $startDate = $employee->last_notif_check ?: $employee->created_at;
        $notificationCount = DB::table('kanban_employee_log')
            ->select(DB::raw('COUNT(kanban_employee_log.id) as count'))
            ->leftJoin('kanban_employees', 'kanban_employee_log.employee_id', '=', 'kanban_employees.id')
            ->leftJoin('kanban_logs', 'kanban_employee_log.log_id', '=', 'kanban_logs.id')
            ->where('kanban_employee_log.employee_id', '=', $employee->id)
            ->where('kanban_employee_log.created_at', '>', $startDate)
            ->first();
        return $notificationCount->count;
    }
}
