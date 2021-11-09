<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Actions\Comments\CreateTaskCommentAction;
use Xguard\LaravelKanban\Actions\Comments\DeleteTaskCommentAction;
use Xguard\LaravelKanban\Actions\Comments\EditTaskCommentAction;
use Xguard\LaravelKanban\Actions\Comments\GetAllTaskCommentsAction;
use Xguard\LaravelKanban\Models\Comment;

class CommentController extends Controller
{

    public function getAllComments()
    {
        return Comment::all();
    }

    public function getAllTaskComments($taskId)
    {
        return (new GetAllTaskCommentsAction(['task_id' => $taskId]))->run();
    }

    public function createOrUpdateTaskComment(Request $request)
    {
        if ($request->filled('id')) {
            return app(EditTaskCommentAction::class)->fill(['comment_data' => $request->all()])->run();
        } else {
            return app(CreateTaskCommentAction::class)->fill(['comment_data' => $request->all()])->run();

        }
    }

    public function deleteTaskComment(Request $request)
    {
        return app(DeleteTaskCommentAction::class)->fill(['comment_data' => $request->all()])->run();
    }
}
