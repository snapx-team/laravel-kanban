<?php

namespace Xguard\LaravelKanban\Actions\Comments;

use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Http\Helper\CheckHasAccessToBoardWithTaskId;
use Xguard\LaravelKanban\Models\Comment;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Lorisleiva\Actions\Action;

class CreateTaskCommentAction extends Action
{

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'comment_data.comment' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'comment_data.comment.required' => 'comment is required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {

        $hasBoardAccess = (new CheckHasAccessToBoardWithTaskId())->returnBool($this->comment_data['taskId']);

        if ($hasBoardAccess) {
            try {
                $comment = Comment::with('task')->create([
                    'task_id' => $this->comment_data['taskId'],
                    'comment' => $this->comment_data['comment'],
                    'employee_id' => session('employee_id'),
                ]);

                $task = Task::with('board')->get()->find($comment->task_id);

                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_COMMENT_CREATED,
                    'Added new comment on task [' . $task->task_simple_name . ']',
                    null,
                    $comment->id,
                    'Xguard\LaravelKanban\Models\Comment'
                );

                if ($this->comment_data['mentions'] != null) {
                    $mentions = array_unique($this->comment_data['mentions']);
                    foreach ($mentions as $mention) {
                        $mentionedEmployee = Employee::with('user')->find($mention);
                        Log::createLog(
                            Auth::user()->id,
                            Log::TYPE_COMMENT_MENTION_CREATED,
                            'Mentioned  user [' . $mentionedEmployee->user->full_name . '] in a comment',
                            $mentionedEmployee->id,
                            $comment->id,
                            'Xguard\LaravelKanban\Models\Comment'
                        );
                    }
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
}
