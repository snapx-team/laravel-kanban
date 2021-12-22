<?php

namespace Xguard\LaravelKanban\Actions\Boards;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Log;

class DeleteBoardAction extends Action
{
    public function rules()
    {
        return [
            'boardId' => ['required', 'integer', 'gt:0'],
        ];
    }

    public function messages()
    {
        return [
            'boardId.required' => 'A Kanban board is required to delete.',
        ];
    }
    public function handle()
    {
        try {
            $board = Board::find($this->boardId);
            $board->delete();

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_BOARD_DELETED,
                'Deleted board [' . $board->name . ']',
                null,
                $board->id,
                'Xguard\LaravelKanban\Models\Board',
            );
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
