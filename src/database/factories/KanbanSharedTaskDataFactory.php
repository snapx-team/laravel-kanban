<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\SharedTaskData;

$factory->define(SharedTaskData::class, function (Faker $faker) {

    return [
        'description' => $faker->sentence(10),
    ];
});
