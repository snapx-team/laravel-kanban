<?php

namespace Xguard\LaravelKanban\Http\Helper;

use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Task;

class CheckHasAccessToBoardWithTaskId
{
    public function returnBool($taskId)
    {
        $task = Task::find($taskId);
        $members = Member::where('board_id', $task->board_id)->get();

        if (session('role') === 'admin') {
            return true;
        } else {
            foreach ($members as $member) {
                if ($member->employee->id === session('employee_id')) {
                    return true;
                }
            }
            return false;
        }
    }
}
