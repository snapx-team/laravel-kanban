<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Badge;

$factory->define(Badge::class, function (Faker $faker) {
    return [
        'name' => $faker->text(rand(10, 20))
    ];
});
