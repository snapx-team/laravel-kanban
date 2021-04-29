<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Xguard\LaravelKanban\Models\PhoneLine;
use Xguard\LaravelKanban\Models\Row;

class PhoneLineController extends Controller
{
    public function createPhoneLine(Request $request)
    {
        $rules = [
            'name' => 'required|unique:kanban_phone_lines,name,'.$request->input('id').',id',
            'phone' => 'required|unique:kanban_phone_lines,name,'.$request->input('id').',id|digits_between:9,11',
        ];

        $customMessages = [
            'name.required' => 'Phone line name is required.',
            'name.unique' => 'Phone line name must be unique.',
            'phone.unique' => 'Phone number already exists.',
            'phone.required' => 'Phone number is required.',
            'phone.digits_between' => 'Phone number must be between 9-11 digits.',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response([
                'success' => 'false',
                'message' => implode(' ', $validator->messages()->all()),
            ], 400);
        }

        try {
            if ($request->filled('id')) {
                PhoneLine::where('id', $request->input('id'))->update([
                    'name' => $request->input('name'),
                    'phone' => $request->input('phone'),
                    'is_active' => $request->input('is_active'),
                    'tag' => !empty($request->input('tag')) ? $request->input('tag') : 'No tag',
                ]);
            } else {
                $phoneLine = PhoneLine::create([
                    'name' => $request->input('name'),
                    'phone' => $request->input('phone'),
                    'is_active' => $request->input('is_active'),
                    'tag' => !empty($request->input('tag')) ? $request->input('tag') : 'No tag',
                ]);
                $daysOfWeek = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");

                foreach ($daysOfWeek as $day) {
                    Row::create(['name' => $day, 'phone_line_id' => $phoneLine->id]);
                }
            }

        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }

        return response(['success' => 'true'], 200);
    }

    public function getTags()
    {
        return PhoneLine::orderBy('tag')->distinct('tag')->pluck('tag');
    }

    public function deletePhoneLine($id)
    {
        try {
            $phoneLine = PhoneLine::find($id);
            $phoneLine->delete();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function getPhoneLines()
    {
        return PhoneLine::orderBy('name')->with('members')->get();
    }
}
