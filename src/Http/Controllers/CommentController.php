<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Models\Employee;

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

    public function getAllTaskAndRelatedTaskComments($taskId)
    {
        //TODO: We may want to add a toggles switch so that a user can see all comments across all associated tasks in one place
    }

    public function createOrUpdateTaskComment(Request $request)
    {
        try {

            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();

            if ($request->filled('id')) {
                Comment::where('id', $request->input('id'))->update([
                    'comment' => $request->input('comment'),
                ]);
            } else {
                Comment::create([
                    'task_id' => $request->input('taskId'),
                    'comment' => $request->input('comment'),
                    'employee_id' => $employee->id,
                ]);
                Log::createLog($comment->employee_id, Log::TYPE_COMMENT_CREATED, 'Added new comment', null, null, $comment->task_id, null, null, null);
            }
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
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
