<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Row;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Models\Task;

$factory->define(Task::class, function (Faker $faker) {

    return [
        'name' => $faker->realText(),
        'index' => null,
        'board_id'=> factory(Board::class),
        'row_id' => factory(Row::class),
        'column_id' => factory(Column::class),
        'reporter_id' => factory(Employee::class),
        'shared_task_data_id' => factory(SharedTaskData::class),
        'deadline' => $faker->dateTimeBetween('+1 week', '+1 month'),
        'status' => 'active'
    ];
});
