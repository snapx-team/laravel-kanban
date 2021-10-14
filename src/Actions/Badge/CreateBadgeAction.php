<?php

namespace Xguard\LaravelKanban\Actions\Badge;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;

class CreateBadgeAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "name" => ['required', 'unique:kanban_badges,name']
        ];
    }

    public function messages()
    {
        return [
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
        $badge = Badge::create([
            'name' => $this->name,
        ]);

        Log::createLog(
            Auth::user()->id,
            Log::TYPE_BADGE_CREATED,
            'Added new badge [' . $badge->name . ']',
            null,
            $badge->id,
            'Xguard\LaravelKanban\Models\Badge'
        );
    }
}
