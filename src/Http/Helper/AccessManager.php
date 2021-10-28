<?php

namespace Xguard\LaravelKanban\Http\Helper;

use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Task;

class AccessManager
{
    public static function canAccessBoard($boardId)
    {
        if (session('role') === 'admin') {
            return true;
        } else {
            $member = Member::where('board_id', $boardId)->where('employee_id', session('employee_id'))->first();
            return $member ? true : false;
        }
    }

    public static function canAccessBoardUsingTaskId($taskId)
    {
        if (session('role') === 'admin') {
            return true;
        } else {
            $task = Task::find($taskId);
            $member = Member::where('board_id', $task->board_id)->where('employee_id', session('employee_id'))->first();
            return $member ? true : false;
        }
    }
}
