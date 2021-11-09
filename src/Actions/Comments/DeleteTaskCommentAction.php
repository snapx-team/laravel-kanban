<?php

namespace Xguard\LaravelKanban\Actions\Comments;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Throwable;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\Comment;
use Xguard\LaravelKanban\Models\Log;

class DeleteTaskCommentAction extends Action
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

    /**
     * @throws Throwable
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();
            $comment = Comment::find($this->comment_data['id']);

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_COMMENT_DELETED,
                $comment->comment,
                null,
                $comment->id,
                'Xguard\LaravelKanban\Models\Comment'
            );

            $comment->delete();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
