<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Template;
use Illuminate\Support\Facades\Validator;

class TemplateController extends Controller
{

    public function getAllTemplates()
    {
        return Template::with('badge')->get();
    }

    public function createOrUpdateTemplate(Request $request)
    {

        $rules = [
            'description' => 'required',
            'name' => 'required'
        ];

        $customMessages = [
            'description.required' => 'Description is required',
            'name.required' => 'Name is required',
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response([
                'success' => 'false',
                'message' => implode(', ', $validator->messages()->all()),
            ], 400);
        }

        try {

            $templateData = $request->all();

            $badge = Badge::firstOrCreate([
                'name' => count($request->input('badge')) > 0 ? $templateData['badge']['name']: '--',
            ]);

            if ($badge->wasRecentlyCreated) {
                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_BADGE_CREATED,
                    "The badge [" . $badge->name . "] was created",
                    $badge->id,
                    null,
                    null,
                    null
                );
            }

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
