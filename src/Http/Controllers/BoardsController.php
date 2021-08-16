<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;

class BoardsController extends Controller
{
    public function createBoard(Request $request)
    {
        $rules = [
            'name' => 'required|unique:kanban_boards,name,' . $request->input('id') . ',id',
        ];

        $customMessages = [
            'name.required' => 'Kanban board name is required.',
            'name.unique' => 'Kanban board name must be unique.',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response([
                'success' => 'false',
                'message' => implode(', ', $validator->messages()->all()),
            ], 400);
        }

        try {
            if ($request->filled('id')) {
                $board = Board::where('id', $request->input('id'))->update([
                    'name' => $request->input('name'),
                ]);
            } else {
                $board = Board::create([
                    'name' => $request->input('name'),
                ]);

                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_BOARD_CREATED,
                    'Added new board [' . $board->name . ']',
                    null,
                    $board->id,
                    null,
                    null,
                    null
                );
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
            $board = Board::find($id);
            $board->delete();

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_BOARD_DELETED,
                'Deleted board [' . $board->name . ']',
                null,
                $board->id,
                null,
                null,
                null
            );
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
        if (session('role') === 'admin') {
            return Board::orderBy('name')->with('members')->get();
        } else {
            return Board::orderBy('name')->
            whereHas('members', function ($q) {
                $q->where('employee_id', session('employee_id'));
            })->with('members')->get();
        }
    }
}
