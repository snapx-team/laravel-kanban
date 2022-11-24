<?php

namespace Xguard\LaravelKanban\Actions\Comments;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Enums\SessionVariables;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\Comment;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Lorisleiva\Actions\Action;

class CreateTaskCommentAction extends Action
{
    const COMMENT_DATA = 'comment_data';
    const MENTIONS = 'mentions';

    public function authorize(): bool
    {
        if (AccessManager::canAccessBoardUsingTaskId($this->comment_data[Comment::TASK_ID])) {
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
                Comment::TASK_ID => $this->comment_data[Comment::TASK_ID],
                Comment::COMMENT => $this->comment_data[Comment::COMMENT],
                Comment::EMPLOYEE_ID => session(SessionVariables::EMPLOYEE_ID()->getValue()),
            ]);

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_COMMENT_CREATED,
                $comment->comment,
                null,
                $comment->id,
                LoggableTypes::COMMENT()->getValue()
            );

            if ($this->comment_data[self::MENTIONS] != null) {
                $mentions = array_unique($this->comment_data[self::MENTIONS]);
                foreach ($mentions as $mention) {
                    $mentionedEmployee = Employee::with(Employee::USER_RELATION_NAME)->find($mention);
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_COMMENT_MENTION_CREATED,
                        'Mentioned user [' . $mentionedEmployee->user->full_name . '] in a comment',
                        $mentionedEmployee->id,
                        $comment->id,
                        LoggableTypes::COMMENT()->getValue()
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
