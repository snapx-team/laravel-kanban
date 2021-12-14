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
        Employee::where('user_id', '=', $userId)->update([
            'last_notif_check' => $lastNotificationCheck
        ]);
    }

    public static function getNotificationCount(int $userId): int
    {
        $employee = Employee::
        where('user_id', '=', $userId)
            ->first();
        if ($employee == null) {
            return 0;
        }
        $startDate = $employee->last_notif_check;
        if ($startDate !== null) {
            $notificationCount = count($employee->notifications->where('created_at', '>=', $startDate));
        } else {
            $notificationCount = count($employee->notifications);
        }
        return $notificationCount;
    }
}
