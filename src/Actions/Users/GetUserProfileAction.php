<?php

namespace Xguard\LaravelKanban\Actions\Users;

use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Task;
use Carbon;
use Lorisleiva\Actions\Action;

class GetUserProfileAction extends Action
{
    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = Auth::user();
        $boardsCount = Member::where('employee_id', session('employee_id'))->count();
        $taskQuery = Task::whereHas('assignedTo', function ($subQuery) {
            $subQuery->where('employee_id', session('employee_id'));
        });
        $taskAssignedTo = (clone $taskQuery)->count();
        $taskCompleted = $taskQuery->where('status', 'completed')->count();
        $taskCreated = Task::where('reporter_id', session('employee_id'))->count();

        return [
            'userName' => $user->full_name,
            'userStatus' => $user->status,
            'userCreatedAt' => Carbon::parse($user->created_at)->toDateString(),
            'boardsCount' => $boardsCount,
            'taskAssignedTo' => $taskAssignedTo,
            'taskComplete' => $taskCompleted,
            'taskCreated' => $taskCreated,
            'language' => $user->locale
        ];
    }
}
