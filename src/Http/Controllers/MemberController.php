<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Actions\Members\DeleteMemberAction;
use Xguard\LaravelKanban\Actions\Members\UpdateOrCreateMemberAction;
use Xguard\LaravelKanban\Models\Member;

class MemberController extends Controller
{
    public function createMembers(Request $request, $boardId)
    {
        return (new UpdateOrCreateMemberAction([
            'members' => $request->all(),
            'board_id' => $boardId
        ]))->run();
    }

    public function deleteMember($id)
    {
        return (new DeleteMemberAction(['member_id' => $id]))->run();
    }

    public function getMembers($id)
    {
        return Member::where('board_id', $id)
            ->with(['employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])->get();
    }
}
