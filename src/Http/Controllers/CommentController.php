<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Http\Helper\CheckHasAccessToBoardWithTaskId;
use Xguard\LaravelKanban\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Task;

class CommentController extends Controller
{

    public function getAllComments()
    {
        return Comment::all();
    }

    public function getAllTaskComments($taskId)
    {
        return Comment::where('task_id', $taskId)->with('employee.user')->orderBy('created_at', 'desc')->get();
    }

    public function createOrUpdateTaskComment(Request $request)
    {
        $taskId = $request->input('taskId');
        $hasBoardAccess = (new CheckHasAccessToBoardWithTaskId())->returnBool($taskId);

        if ($hasBoardAccess) {
            try {
                if ($request->filled('id')) {
                    $comment = Comment::where('id', $request->input('id'))->update([
                        'comment' => $request->input('comment'),
                    ]);
                } else {
                    $comment = Comment::create([
                        'task_id' => $request->input('taskId'),
                        'comment' => $request->input('comment'),
                        'employee_id' => session('employee_id'),
                    ]);
                    Log::createLog($comment->employee_id, Log::TYPE_COMMENT_CREATED, 'Added new comment', null, null, $comment->task_id, null, null, null);
                }
            } catch (\Exception $e) {
                return response([
                    'success' => 'false',
                    'message' => $e->getMessage(),
                ], 400);
            }
        } else {
            return response([
                'success' => 'false',
                'message' => 'You don\'t have access to this board',
            ], 400);
        }


        return response(['success' => 'true'], 200);
    }

    public function deleteTaskComment($id)
    {
        try {
            $comment = Comment::find($id);
            $comment->delete();

            Log::createLog($comment->employee_id, Log::TYPE_COMMENT_DELETED, 'Deleted comment', null, null, $comment->task_id, null, null, null);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }
}
