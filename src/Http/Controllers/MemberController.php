<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Log;

class MemberController extends Controller
{
    public function createMembers(Request $request, $boardId)
    {
        $members = $request->all();
        try {
            foreach ($members as $employee) {
                $member = Member::firstOrCreate([
                    'employee_id' => $employee['id'],
                    'board_id' => $boardId,
                ]);

                Log::createLog($member->employee_id, Log::TYPE_KANBAN_MEMBER_CREATED, 'Added a new member', null, $member->board_id, null, null, null, null);
            }
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function deleteMember($id)
    {
        try {
            $member = Member::find($id);
            $member->delete();

            Log::createLog($member->employee_id, Log::TYPE_KANBAN_MEMBER_DELETED, 'Deleted a member', null, $member->board_id, null, null, null, null);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function getMembers($id)
    {
        return Member::where('board_id', $id)->with('employee.user')->get();
    }
}
