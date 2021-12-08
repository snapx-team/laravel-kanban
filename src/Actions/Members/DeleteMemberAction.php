<?php

namespace Xguard\LaravelKanban\Actions\Members;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Member;
use Illuminate\Support\Facades\Auth;

class DeleteMemberAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "memberId" => ['required', 'integer', 'gt:0'],
        ];
    }
    
    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();
            $member = Member::findOrFail($this->memberId);
            $member->delete();

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_KANBAN_MEMBER_DELETED,
                'Deleted member [' . $member->employee->user->full_name . '] from board [' . $member->board->name . ']',
                $member->employee_id,
                $member->board_id,
                'Xguard\LaravelKanban\Models\Board'
            );
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
