<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Member;

$factory->define(Member::class, function (Faker $faker) {

    return [
        'employee_id' => factory(Employee::class),
        'board_id' => factory(Board::class)
    ];
});
