<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Row;

$factory->define(Column::class, function (Faker $faker) {

    return [
        'name' => $faker->word,
        'index' => 0,
        'row_id' => factory(Row::class),
    ];
});
