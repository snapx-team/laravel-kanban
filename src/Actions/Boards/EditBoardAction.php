<?php

namespace Xguard\LaravelKanban\Actions\Boards;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Log;

class EditBoardAction extends Action
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
            'boardId.required' => 'A Kanban board is required to edit.',
        ];
    }

    public function handle()
    {
        try {
            \DB::beginTransaction();
            if ($this->boardId || $this->boardName) {
                $board = Board::where('id', $this->boardId)->first();
                $board->update(['name' => $this->boardName]);

                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_BOARD_EDITED,
                    'Edited board [' . $board->name . ']',
                    null,
                    $board->id,
                    'Xguard\LaravelKanban\Models\Board'
                );
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
