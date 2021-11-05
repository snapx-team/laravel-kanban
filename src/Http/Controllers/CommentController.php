<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Actions\Comments\CreateTaskCommentAction;
use Xguard\LaravelKanban\Actions\Comments\EditTaskCommentAction;
use Xguard\LaravelKanban\Actions\Comments\GetAllTaskCommentsAction;
use Xguard\LaravelKanban\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

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
            return (new EditTaskCommentAction(['comment_data' => $request->all()]))->run();
        } else {
            return (new CreateTaskCommentAction(['comment_data' => $request->all()]))->run();
        }
    }

    public function deleteTaskComment($id)
    {
        try {
            $comment = Comment::find($id);

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_COMMENT_DELETED,
                $comment->comment,
                null,
                $comment->id,
                'Xguard\LaravelKanban\Models\Comment'
            );

            $comment->delete();

        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }
}
