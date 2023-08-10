<?php

namespace Xguard\LaravelKanban\Actions\Comments;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Throwable;
use Xguard\LaravelKanban\Models\Comment;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;

class EditTaskCommentAction extends Action
{
    public function authorize(): bool
    {
        if ($this->comment_data['employee_id'] === session('employee_id')) {
            return true;
        }
        return false;
    }

    /**
     * @throws AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException('You cannot delete another user\'s comment');
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

            $comment = Comment::find($this->comment_data['id']);

            if ($comment === null) {
                return;
            }

            $comment->update(['comment' => $this->comment_data['comment']]);
            $comment->refresh();

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_COMMENT_EDITED,
                $comment->comment,
                null,
                $comment->id,
                'Xguard\LaravelKanban\Models\Comment'
            );

            if ($this->comment_data['mentions'] != null) {
                $mentions = array_unique($this->comment_data['mentions']);
                foreach ($mentions as $mention) {
                    $mentionExistedBeforeEdit = Log::where('targeted_employee_id', $mention)
                        ->where('loggable_id', $comment->id)
                        ->where('loggable_type', 'Xguard\LaravelKanban\Models\Comment')
                        ->first();
                    if ($mentionExistedBeforeEdit) {
                        $mentionedEmployee = Employee::find($mention);
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
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
