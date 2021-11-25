<?php

namespace Xguard\LaravelKanban\Actions\Templates;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Actions\Badges\CreateBadgeAction;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Template;

class CreateOrUpdateTemplateAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'description' => ['required', 'string'],
            'name' => ['required', 'string'],
            'taskName' => ['required', 'string'],
            'templateId' => ['nullable', 'integer', 'gt:0'],
            'badge' => ['present', 'array'],
            'boards' => ['nullable', 'array'],
            'checkedOptions' => ['present', 'array'],
        ];
    }

    public function messages()
    {
        return [
            'description.required' => 'Description is required',
            'name.required' => 'Template name is required',
            'task_name.required' => 'Task name is required',
        ];
    }
    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();
            $badgeName = count($this->badge) > 0 ? trim($this->badge['name']) : '--';
            $badge = Badge::where('name', $badgeName)->first();

            if (!$badge) {
                $badge = app(CreateBadgeAction::class)->fill(["name" => $badgeName])->run();
            }

            $template = Template::updateOrCreate(
                ['id' => $this->templateId],
                [
                    'name' => $this->name,
                    'task_name' => $this->taskName,
                    'badge_id' => $badge->id,
                    'description' => $this->description,
                    'options' => serialize($this->checkedOptions),
                ]
            );

            if ($template->wasRecentlyCreated) {
                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_TEMPLATE_CREATED,
                    'Created template [' .$template->name. ']',
                    null,
                    $template->id,
                    'Xguard\LaravelKanban\Models\Template'
                );
            } else {
                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_TEMPLATE_UPDATED,
                    'Updated template [' .$template->name. ']',
                    null,
                    $template->id,
                    'Xguard\LaravelKanban\Models\Template'
                );
            }

            if ($this->boards !== null) {
                $kanbanArray = [];
                foreach ($this->boards as $kanban) {
                    array_push($kanbanArray, $kanban['id']);
                }
                $template->boards()->sync($kanbanArray);
            }
            \DB::commit();
            return $template;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
