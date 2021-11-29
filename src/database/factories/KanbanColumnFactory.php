<?php

use Faker\Generator as Faker;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Row;

$factory->define(Column::class, function (Faker $faker, $attributes) {
    $row_id = Arr::get($attributes, 'row_id') ?? factory(Row::class)->create()->id;
    return [
        'name' => $attributes['name'] ?? $faker->unique()->text(),
        'row_id' => $row_id,
        'index' => rand(1, 10)
    ];
});
