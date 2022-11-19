<?php

namespace Xguard\LaravelKanban\Repositories;

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
        Employee::where(Employee::USER_ID, '=', $userId)->update([
            Employee::LAST_NOTIF_CHECK => $lastNotificationCheck
        ]);
    }

    public static function getNotificationCount(int $userId): int
    {
        $employee = Employee::where(Employee::USER_ID, '=', $userId)->first();
        $startDate = $employee->last_notif_check ?: $employee->created_at;
        $employeeWithNotificationCount = Employee::where(Employee::USER_ID, '=', $userId)->withCount([
            Employee::NOTFICIATIONS_RELATION_NAME => function ($q) use ($startDate) {
                $q->where('kanban_logs.created_at', '>=', $startDate);
            }])->first();
        return $employeeWithNotificationCount->notifications_count;
    }
}
