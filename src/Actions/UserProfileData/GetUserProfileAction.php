<?php

namespace Xguard\LaravelKanban\Actions\UserProfileData;

use Xguard\LaravelKanban\Models\Employee;
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
        $boardsCount = Member::where('employee_id', session('employee_id'))->count();
        $taskQuery = Task::whereHas('assignedTo', function ($subQuery) {
            $subQuery->where('employee_id', session('employee_id'));
        });
        $taskAssignedTo = (clone $taskQuery)->count();
        $taskCompleted = $taskQuery->where('status', 'completed')->count();
        $taskCreated = Task::where('reporter_id', session('employee_id'))->count();
        $employee = Employee::with('user')->get()->find(session('employee_id'));

        return [
            'userName' => $employee->user->full_name,
            'userStatus' => $employee->role,
            'userCreatedAt' => Carbon::parse($employee->created_at)->toDateString(),
            'boardsCount' => $boardsCount,
            'taskAssignedTo' => $taskAssignedTo,
            'taskComplete' => $taskCompleted,
            'taskCreated' => $taskCreated,
            'language' => $employee->user->locale
        ];
    }
}
