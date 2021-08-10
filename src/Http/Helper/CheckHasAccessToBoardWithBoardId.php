<?php

namespace Xguard\LaravelKanban\Http\Helper;

use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Task;

class CheckHasAccessToBoardWithBoardId
{
    public function returnBool($boardId)
    {
        $members = Member::where('board_id', $boardId)->get();

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
