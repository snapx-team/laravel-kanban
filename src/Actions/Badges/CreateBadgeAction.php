<?php

namespace Xguard\LaravelKanban\Actions\Badges;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Enums\Roles;
use Xguard\LaravelKanban\Enums\SessionVariables;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;

class CreateBadgeAction extends Action
{
    const NAME = 'name';

    public function authorize()
    {
        return session(SessionVariables::ROLE()->getValue()) === Roles::ADMIN()->getValue();
    }

    public function rules() :array
    {
        return [
            self::NAME => ['required', 'string', 'unique:kanban_badges,name']
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Kanban badge name is required',
            'name.unique' => 'Kanban badge name must be unique',
        ];
    }

    public function handle()
    {
        $badge = Badge::create([
            Badge::NAME => $this->name,
        ]);

        Log::createLog(
            Auth::user()->id,
            Log::TYPE_BADGE_CREATED,
            'Added new badge [' . $badge->name . ']',
            null,
            $badge->id,
            LoggableTypes::BADGE()->getValue()
        );

        return $badge;
    }
}
