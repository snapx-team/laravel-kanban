<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Row;

class BoardsController extends Controller
{
    public function createBoard(Request $request)
    {
        $rules = [
            'name' => 'required|unique:kanban_boards,name,'.$request->input('id').',id',
        ];

        $customMessages = [
            'name.required' => 'Kanban board name is required.',
            'name.unique' => 'Kanban board name must be unique.',
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
                Board::where('id', $request->input('id'))->update([
                    'name' => $request->input('name'),
                ]);
            } else {
                $phoneLine = Board::create([
                    'name' => $request->input('name'),
                ]);
                $daysOfWeek = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");

                foreach ($daysOfWeek as $day) {
                    Row::create(['name' => $day, 'board_id' => $phoneLine->id]);
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
        return Board::orderBy('tag')->distinct('tag')->pluck('tag');
    }

    public function deleteBoard($id)
    {
        try {
            $phoneLine = Board::find($id);
            $phoneLine->delete();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function getBoards()
    {
        return Board::orderBy('name')->with('members')->get();
    }
}
