<?php

namespace Xguard\LaravelKanban\Actions\Templates;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Models\Template;

class DeleteTemplateAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'templateId' => ['required', 'integer', 'gt:0'],
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
            $template = Template::findOrFail($this->templateId);
            $template->delete();

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_TEMPLATE_DELETED,
                'Deleted template [' .$template->name. ']',
                null,
                $template->id,
                'Xguard\LaravelKanban\Models\Template'
            );
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
