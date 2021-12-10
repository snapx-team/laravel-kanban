<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\TaskFile;

$factory->define(TaskFile::class, function (Faker $faker) {

    return [
        'task_id' => factory(Task::class),
        'task_file_url' => '/' . implode('/', $faker->words($faker->numberBetween(0, 4))),
    ];
});
