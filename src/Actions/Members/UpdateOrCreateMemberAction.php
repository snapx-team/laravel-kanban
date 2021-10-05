<?php

namespace Xguard\LaravelKanban\Actions\Members;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Member;
use Lorisleiva\Actions\Action;

class UpdateOrCreateMemberAction extends Action
{

    /**
     * Execute the action and return a result.
     *
     * @return Application|ResponseFactory|Response
     */
    public function handle()
    {
        try {
            foreach ($this->members as $employee) {
                $member = Member::with('employee.user', 'board')->firstOrCreate([
                    'employee_id' => $employee['id'],
                    'board_id' => $this->board_id,
                ]);

                if ($member->wasRecentlyCreated) {
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_KANBAN_MEMBER_CREATED,
                        'Added a new member [' . $member->employee->user->full_name . '] to board [' . $member->board->name . ']',
                        null,
                        $member->board_id,
                        null,
                        $member->employee_id
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
}
