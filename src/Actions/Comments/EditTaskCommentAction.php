<?php

namespace Xguard\LaravelKanban\Actions\Comments;

use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Http\Helper\CheckHasAccessToBoardWithTaskId;
use Xguard\LaravelKanban\Models\Comment;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Lorisleiva\Actions\Action;

class EditTaskCommentAction extends Action
{

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        //TODO
    }
}
