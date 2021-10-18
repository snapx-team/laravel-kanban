<?php

namespace Xguard\LaravelKanban\Actions\Comments;

use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Models\Comment;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Task;
use Carbon;
use Lorisleiva\Actions\Action;

class GetAllTaskCommentsAction extends Action
{
    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        // this will also get all comments of related tasks
        $sharedTaskDataId = Task::find($this->task_id)->shared_task_data_id;
        $allAssociatedTasks = Task::where('shared_task_data_id', '=', $sharedTaskDataId)->pluck('id')->toArray();
        return Comment::whereIn('task_id', $allAssociatedTasks)
            ->with('employee.user')
            ->with(['task' => function ($q) {
                $q->with('board');
            }])
            ->orderBy('created_at', 'desc')->get();
    }
}
