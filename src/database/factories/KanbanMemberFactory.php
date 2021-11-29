<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Member;

$factory->define(Member::class, function (Faker $faker, $attributes) {

    $employee_id = Arr::get($attributes, 'employee_id')
        ?? factory(Employee::class)->create()->id;

    $board_id = Arr::get($attributes, 'employee_id')
        ?? factory(Board::class)->create()->id;

    return [
        'employee_id' => $employee_id,
        'board_id' => $board_id
    ];
});
