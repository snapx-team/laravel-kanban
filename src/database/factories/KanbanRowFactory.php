<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Row;

$factory->define(Row::class, function (Faker $faker) {
    return [
        'name' => $attributes['name'] ?? $faker->unique()->text(),
        'board_id' => factory(Board::class),
        'index' => rand(1, 10)
    ];
});
