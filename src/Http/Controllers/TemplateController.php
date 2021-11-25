<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Actions\Templates\CreateOrUpdateTemplateAction;
use Xguard\LaravelKanban\Actions\Templates\DeleteTemplateAction;
use Xguard\LaravelKanban\Models\Template;

class TemplateController extends Controller
{

    public function getAllTemplates()
    {
        return Template::orderBy('name')->with('badge', 'boards')->get();
    }

    public function createOrUpdateTemplate(Request $request)
    {
        try {
            $templateData = $request->all();
            app(CreateOrUpdateTemplateAction::class)->fill([
                'description' => $templateData['description'],
                'name' => $templateData['name'],
                'taskName' => $templateData['task_name'],
                'templateId' => $templateData['id'],
                'badge' => $templateData['badge'],
                'boards' => $templateData['boards'],
                'checkedOptions' => $templateData['checkedOptions'],
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function deleteTemplate($id)
    {
        try {
            app(DeleteTemplateAction::class)->fill([
                'templateId' => $id
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }
}
