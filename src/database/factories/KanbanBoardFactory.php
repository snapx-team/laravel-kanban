<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Board;

$factory->define(Board::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->text(),
    ];
});
