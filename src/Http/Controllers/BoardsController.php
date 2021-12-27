<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Actions\Boards\CreateBoardAction;
use Xguard\LaravelKanban\Actions\Boards\DeleteBoardAction;
use Xguard\LaravelKanban\Actions\Boards\EditBoardAction;
use Xguard\LaravelKanban\Repositories\BoardsRepository;

class BoardsController extends Controller
{
    public function createBoard(Request $request)
    {
        try {
            app(CreateBoardAction::class)->fill([
                'boardName' => $request->input('name'),
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function editBoard(Request $request)
    {
        try {
            app(EditBoardAction::class)->fill([
                'boardId' => $request->input('id'),
                'boardName' => $request->input('name'),
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function deleteBoard($id)
    {
        try {
            app(DeleteBoardAction::class)->fill([
                'boardId' => $id,
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function getBoards()
    {
        return BoardsRepository::getBoards();
    }
}
