<?php

/** @var Factory $factory */

use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Employee;

$factory->define(Employee::class, function (Faker $faker) {

    return [
        'user_id' => factory(User::class),
        'role' => 'employee'
    ];
});

$factory->state(Employee::class, 'admin', function (Faker $faker) {
    return [
        'role' => 'admin',
    ];
});
