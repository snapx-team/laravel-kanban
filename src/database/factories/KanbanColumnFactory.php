<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Row;

$factory->define(Column::class, function (Faker $faker) {
    return [
        'name' => $attributes['name'] ?? $faker->unique()->text(),
        'row_id' => factory(Row::class),
        'index' => rand(1, 10)
    ];
});
