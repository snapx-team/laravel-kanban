<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Models\Task;

$factory->define(Task::class, function (Faker $faker) {

    return [
        'name' => $faker->realText(),
        'board_id'=> factory(Board::class),
        'reporter_id' => factory(Employee::class),
        'shared_task_data_id' => factory(SharedTaskData::class)
    ];
});
