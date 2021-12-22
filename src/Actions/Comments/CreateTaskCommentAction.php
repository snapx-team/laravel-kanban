<?php

namespace Xguard\LaravelKanban\Actions\Comments;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\Comment;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Lorisleiva\Actions\Action;

class CreateTaskCommentAction extends Action
{

    public function authorize(): bool
    {
        if (AccessManager::canAccessBoardUsingTaskId($this->comment_data['task_id'])) {
            return true;
        }
        return false;
    }

    /**
     * @throws AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException('You don\'t have access to this board');
    }

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
     * @throws Throwable
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();
            $comment = Comment::create([
                'task_id' => $this->comment_data['task_id'],
                'comment' => $this->comment_data['comment'],
                'employee_id' => session('employee_id'),
            ]);

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_COMMENT_CREATED,
                $comment->comment,
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
                        'Mentioned user [' . $mentionedEmployee->user->full_name . '] in a comment',
                        $mentionedEmployee->id,
                        $comment->id,
                        'Xguard\LaravelKanban\Models\Comment'
                    );
                }
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
