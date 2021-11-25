<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */


use Faker\Generator as Faker;
use Xguard\LaravelKanban\Models\Badge;

$factory->define(Badge::class, function (Faker $faker, $attributes) {
    return [
        'name' => $attributes['name'] ?? $faker->text(rand(10, 20))
    ];
});
