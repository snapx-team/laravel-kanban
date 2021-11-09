<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Comment;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;

$factory->define(Comment::class, function (Faker $faker) {

    return [
        'task_id' => factory(Task::class),
        'employee_id' => factory(Employee::class),
        'comment' => $faker->sentence(10),
    ];
});
