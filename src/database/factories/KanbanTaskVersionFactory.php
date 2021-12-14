<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\TaskVersion;

$factory->define(TaskVersion::class, function (Faker $faker) {

    return [
        'name' => $faker->realText(),
        'task_id'=> factory(Task::class),
        'log_id' => factory(Log::class),
    ];
});
