<?php

/** @var Factory $factory */

use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

$factory->define(Log::class, function (Faker $faker) {

    return [
        'user_id' => factory(User::class),
        'log_type' => LOG::TYPE_CARD_COMPLETED,
        'description' => $faker->sentence(10),
        'targeted_employee_id' => factory(Employee::class),
        'loggable_id' => factory(Task::class),
        'loggable_type' => 'Xguard\LaravelKanban\Models\Task'
        ];
});
