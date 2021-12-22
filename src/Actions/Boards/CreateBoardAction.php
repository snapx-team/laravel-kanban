<?php

namespace Xguard\LaravelKanban\Actions\Boards;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Log;

class CreateBoardAction extends Action
{

    public function rules()
    {
        return [
            'boardName' => 'required|unique:kanban_boards,name',
        ];
    }

    public function messages()
    {
        return [
            'boardName.required' => 'Kanban board name is required.',
            'boardName.unique' => 'Kanban board name must be unique.',
        ];
    }

    public function handle()
    {
        try {
            \DB::beginTransaction();
            if ($this->boardName) {
                $board = Board::create([
                    'name' => $this->boardName,
                ]);

                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_BOARD_CREATED,
                    'Created new board [' . $board->name . ']',
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
