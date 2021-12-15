<?php

namespace Xguard\LaravelKanban\Repositories;

use Xguard\LaravelKanban\Models\EmployeeBoardNotificationSetting;

class EmployeeBoardNotificationSettingRepository
{
    public static function updateOrCreate(array $matchingAttributes, array $payload): EmployeeBoardNotificationSetting
    {
        return EmployeeBoardNotificationSetting::updateOrCreate($matchingAttributes, $payload);
    }
}
