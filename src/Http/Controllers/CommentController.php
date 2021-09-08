<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Xguard\LaravelKanban\Http\Helper\CheckHasAccessToBoardWithTaskId;
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
        // this will also get all comments of related tasks
        $sharedTaskDataId = Task::find($taskId)->shared_task_data_id;
        $allAssociatedTasks = Task::where('shared_task_data_id', '=', $sharedTaskDataId)->pluck('id')->toArray();
        return Comment::whereIn('task_id', $allAssociatedTasks)
            ->with('employee.user')
            ->with(['task' => function ($q) {
                $q->with('board');
            }])
            ->orderBy('created_at', 'desc')->get();
    }

    public function createOrUpdateTaskComment(Request $request)
    {

        $rules = [
            'comment' => 'required'
        ];

        $customMessages = [
            'comment.required' => 'Comment cannot be empty',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response([
                'success' => 'false',
                'message' => implode(', ', $validator->messages()->all()),
            ], 400);
        }

        $taskId = $request->input('taskId');
        $hasBoardAccess = (new CheckHasAccessToBoardWithTaskId())->returnBool($taskId);

        if ($hasBoardAccess) {
            try {
                if ($request->filled('id')) {
                    $prevComment = Comment::find($request->input('id'));

                    $comment = Comment::where('id', $request->input('id'))->update([
                        'comment' => $request->input('comment'),
                    ]);

                    $task = Task::with('board')->get()->find($comment->task_id);
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_COMMENT_EDITED,
                        'Edited comment on task [' . $task->task_simple_name . ']',
                        null,
                        $task->board_id,
                        $comment->task_id,
                        null
                    );

                } else {
                    $comment = Comment::with('task')->create([
                        'task_id' => $request->input('taskId'),
                        'comment' => $request->input('comment'),
                        'employee_id' => session('employee_id'),
                    ]);

                    $task = Task::with('board')->get()->find($comment->task_id);
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_COMMENT_CREATED,
                        'Added new comment on task [' . $task->task_simple_name . ']',
                        null,
                        $task->board_id,
                        $comment->task_id,
                        null
                    );
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

            $task = Task::with('board')->get()->find($comment->task_id);

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_COMMENT_DELETED,
                'Deleted comment on task [' . $task->task_simple_name . ']',
                null,
                $task->board_id,
                $comment->task_id,
                null
            );
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }
}
