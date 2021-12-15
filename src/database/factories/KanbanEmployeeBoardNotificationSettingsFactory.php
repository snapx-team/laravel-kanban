<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\EmployeeBoardNotificationSetting;

$factory->define(EmployeeBoardNotificationSetting::class, function (Faker $faker) {
    
    return [
        'board_id' => factory(Board::class),
        'employee_id' => factory(Employee::class),
        'ignore_types' => "a:1:{i:0;s:17:\"TASK_STATUS_GROUP\";}"
    ];
});
