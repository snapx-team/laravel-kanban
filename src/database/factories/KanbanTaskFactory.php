<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Row;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Models\Task;

$factory->define(Task::class, function (Faker $faker, $attributes) {

    $status = Arr::get($attributes, 'status') ?? 'active';
    $board_id = Arr::get($attributes, 'board_id') ? factory(Board::class)->create(['id' => Arr::get($attributes, 'board_id')])->id: factory(Board::class)->create()->id;
    $row_id = factory(Row::class)->create(['board_id' => $board_id])->id;
    $column_id = factory(Column::class)->create(['row_id' => $row_id])->id;
    $shared_task_data_id = Arr::get($attributes, 'shared_task_data_id') ?? factory(SharedTaskData::class)->create()->id;
    $index =  Arr::get($attributes, 'index') ?? null;
    $reporter_id = Arr::get($attributes, 'reporter_id') ?? factory(Employee::class)->create()->id;
    $deadline =  Arr::get($attributes, 'deadline') ? date('y-m-d h:m', strtotime(Arr::get($attributes, 'deadline'))) : factory(Employee::class)->create()->id;

    return [
        'name' => $faker->realText(),
        'index' => $index,
        'board_id'=> $board_id,
        'row_id' => $row_id,
        'column_id' => $column_id,
        'reporter_id' => $reporter_id,
        'shared_task_data_id' => $shared_task_data_id,
        'deadline' => $deadline,
        'status' => $status
    ];
});
