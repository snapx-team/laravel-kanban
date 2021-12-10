<?php

namespace Xguard\LaravelKanban\Actions\Members;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Member;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;

class CreateMembersAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "employees" => ['present', 'array'],
            "boardId" => ['required', 'integer', 'gt:0'],
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
            $board = Board::findOrFail($this->boardId);
            foreach ($this->employees as $employee) {
                $employee = Employee::findOrFail($employee['id']);
                $member = Member::firstOrCreate([
                    'employee_id' => $employee->id,
                    'board_id' => $board->id,
                ]);

                if ($member->wasRecentlyCreated) {
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
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
