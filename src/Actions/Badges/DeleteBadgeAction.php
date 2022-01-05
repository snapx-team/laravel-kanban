<?php

namespace Xguard\LaravelKanban\Actions\Badges;

use Exception;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\Template;

class DeleteBadgeAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'badge_id'   => ['required', 'integer', 'gt:0']
        ];
    }
    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $badge = Badge::findOrFail($this->badge_id);
        $task = Task::where(Task::BADGE_ID, $this->badge_id)->first();
        $template = Template::where(Template::BADGE_ID, $this->badge_id)->first();
        if ($task || $template) {
            throw new Exception('Can\'t delete a badge that is currently used');
        }
        $badge->delete();

        Log::createLog(
            Auth::user()->id,
            Log::TYPE_BADGE_DELETED,
            'Deleted badge [' . $badge->name . ']',
            null,
            $badge->id,
            LoggableTypes::BADGE()->getValue()
        );
    }
}
