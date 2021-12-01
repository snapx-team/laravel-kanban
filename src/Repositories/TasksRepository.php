<?php

namespace Xguard\LaravelKanban\Repositories;

use Carbon;
use Xguard\LaravelKanban\Models\TaskVersion;

class TasksRepository
{
    public static function versionTask($taskData)
    {
        TaskVersion::create([
            "index" => $taskData['index'],
            "name" => $taskData['name'],
            "deadline" => Carbon::parse($taskData['deadline'])->toDateTimeString(),
            "shared_task_data_id" =>$taskData['shared_task_data_id'],
            "reporter_id" => $taskData['reporter_id'],
            "column_id" => $taskData['column_id'],
            "row_id" => $taskData['row_id'],
            "board_id" => $taskData['board_id'],
            "badge_id" => $taskData['badge_id'],
            "status" => $taskData['status'] ? $taskData['status'] : 'active',
            "task_id" => $taskData['task_id'],
            "log_id" => $taskData ['log_id']
        ]);
    }
}
