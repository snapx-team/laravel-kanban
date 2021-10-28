<?php

namespace Xguard\LaravelKanban\Actions\Badges;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;

class EditBadgeAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'badge_id'   => ['required', 'integer', 'gt:0'],
            'name' => ['required', 'unique:kanban_badges,name,'.$this->badge_id]
        ];
    }

    public function messages()
    {
        return [
            'badge_id.required' => 'Kanban badge id is required',
            'name.required' => 'Kanban badge name is required',
            'name.unique' => 'Kanban badge name must be unique',
        ];
    }
    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $badge= Badge::where('id', $this->badge_id)->first();
        $oldName = (clone $badge)->name;
        $badge->update(['name' => $this->name]);

        Log::createLog(
            Auth::user()->id,
            Log::TYPE_BADGE_EDITED,
            'Changed badge name from [' . $oldName . '] to [' . $badge->name . ']',
            null,
            $badge->id,
            'Xguard\LaravelKanban\Models\Badge'
        );
    }
}
