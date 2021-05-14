<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\Member;

class MemberController extends Controller
{
    public function createMembers(Request $request, $boardId)
    {
        $members = $request->all();
        try {
            foreach ($members as $employee) {
                Member::firstOrCreate([
                    'employee_id' => $employee['id'],
                    'board_id' => $boardId,
                ]);
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
        return Member::where('board_id', $id)->with('employee')->get();
    }
}
