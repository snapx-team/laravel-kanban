<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Template;

$factory->define(Template::class, function (Faker $faker) {

    return [
        'name' => $faker->text(rand(10, 20)),
        'task_name' => $faker->text(rand(10, 20)),
        'description' => $faker->text(rand(10, 20)),
        'options' => '',
        'badge_id' => factory(Badge::class)
    ];
});
