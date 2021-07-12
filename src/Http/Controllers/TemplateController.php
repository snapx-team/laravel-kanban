<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Template;

class TemplateController extends Controller
{

    public function getAllTemplates()
    {
        return Template::with('badge')->get();
    }

    public function createOrUpdateTemplate(Request $request)
    {
        try {

            $templateData = $request->all();

            $badge = Badge::firstOrCreate([
                'name' => $templateData['badge']['name'],
            ]);

            if ($request->filled('id')) {
                Template::where('id', $request->input('id'))->update([
                    'name' => $request->input('name'),
                    'badge_id' => $badge->id,
                    'description' => $request->input('description'),
                    'options' => serialize($request->input('checkedOptions')),
                ]);
            } else {
                Template::create([
                    'name' => $request->input('name'),
                    'badge_id' => $badge->id,
                    'description' => $request->input('description'),
                    'options' => serialize($request->input('checkedOptions')),
                ]);
            }
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
            $template = Template::find($id);
            $template->delete();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

}
