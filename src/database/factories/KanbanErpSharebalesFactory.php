<?php

/** @var Factory $factory */

use App\Models\Contract;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\ErpShareables;
use Xguard\LaravelKanban\Models\SharedTaskData;

$factory->define(ErpShareables::class, function (Faker $faker) {

    return [
        'shareable_type'=> 'user',
        'shareable_id' => factory(User::class),
        'shared_task_data_id' => factory(SharedTaskData::class)
    ];
});

$factory->state(ErpShareables::class, 'contract', function (Faker $faker) {
    return [
        'shareable_type'=> 'contract',
        'shareable_id' => factory(Contract::class),
        'shared_task_data_id' => factory(SharedTaskData::class)
    ];
});
