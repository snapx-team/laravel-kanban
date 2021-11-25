<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */


use Faker\Generator as Faker;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Template;

$factory->define(Template::class, function (Faker $faker, $attributes) {
    $badge_id = Arr::get($attributes, 'badge_id')
        ?? factory(Badge::class)->create()->id;

    return [
        'name' => $faker->text(rand(10, 20)),
        'task_name' => $faker->text(rand(10, 20)),
        'description' => $faker->text(rand(10, 20)),
        'options' => '',
        'badge_id' => $badge_id
    ];
});
