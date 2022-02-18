<?php

namespace Xguard\LaravelKanban\Repositories;

use Carbon;
use Xguard\LaravelKanban\Models\TaskVersion;

class TasksRepository
{
    const INDEX = "index";
    const NAME = "name";
    const DEADLINE = "deadline";
    const SHARED_TASK_DATA_ID = "shared_task_data_id";
    const REPORTER_ID = "reporter_id";
    const COLUMN_ID = "column_id";
    const ROW_ID = "row_id";
    const BOARD_ID = "board_id";
    const BADGE_ID = "badge_id";
    const STATUS = "status";
    const TASK_ID = "task_id";
    const LOG_ID = "log_id";
    const TIME_ESTIMATE = "time_estimate";

    public static function versionTask($taskData)
    {
        TaskVersion::create([
            self::INDEX => $taskData['index'],
            self::NAME => $taskData['name'],
            self::DEADLINE => Carbon::parse($taskData['deadline'])->toDateTimeString(),
            self::SHARED_TASK_DATA_ID =>$taskData['shared_task_data_id'],
            self::REPORTER_ID => $taskData['reporter_id'],
            self::COLUMN_ID => $taskData['column_id'],
            self::ROW_ID => $taskData['row_id'],
            self::BOARD_ID => $taskData['board_id'],
            self::BADGE_ID => $taskData['badge_id'],
            self::STATUS => $taskData['status'] ? $taskData['status'] : 'active',
            self::TASK_ID => $taskData['task_id'],
            self::LOG_ID => $taskData ['log_id'],
            self::TIME_ESTIMATE => $taskData ['time_estimate']
        ]);
    }
}
