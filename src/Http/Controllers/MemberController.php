<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function createMembers(Request $request, $boardId)
    {
        $members = $request->all();
        try {
            foreach ($members as $employee) {
                $member = Member::with('employee.user', 'board')->firstOrCreate([
                    'employee_id' => $employee['id'],
                    'board_id' => $boardId,
                ]);

                if($member->wasRecentlyCreated) {
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_KANBAN_MEMBER_CREATED,
                        'Added a new member [' . $member->employee->user->full_name . '] to board [' . $member->board->name . ']',
                        $member->employee_id,
                        $member->board_id,
                        'Xguard\LaravelKanban\Models\Board'
                    );
                }
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
            $member = Member::with('employee.user', 'board')->get()->find($id);
            $member->delete();

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_KANBAN_MEMBER_DELETED,
                'Deleted member [' . $member->employee->user->full_name . '] from board [' . $member->board->name . ']',
                $member->employee_id,
                $member->board_id,
                'Xguard\LaravelKanban\Models\Board'
            );

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
        return Member::where('board_id', $id)
            ->with(['employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])->get();
    }
}
