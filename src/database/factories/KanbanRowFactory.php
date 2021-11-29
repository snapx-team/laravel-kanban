<?php

use Faker\Generator as Faker;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Row;

$factory->define(Row::class, function (Faker $faker, $attributes) {
    $board_id = Arr::get($attributes, 'board_id') ?? factory(Board::class)->create()->id;
    return [
        'name' => $attributes['name'] ?? $faker->unique()->text(),
        'board_id' => $board_id,
        'index' => rand(1, 10)
    ];
});
