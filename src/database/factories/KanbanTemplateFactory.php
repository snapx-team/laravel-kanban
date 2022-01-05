<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Template;

$factory->define(Template::class, function (Faker $faker) {

    return [
        'name' => $faker->text(),
        'task_name' => $faker->text(),
        'description' => $faker->text(),
        'options' => '',
        'badge_id' => factory(Badge::class)
    ];
});
