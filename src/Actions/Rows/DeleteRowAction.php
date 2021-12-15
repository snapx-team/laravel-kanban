<?php

namespace Xguard\LaravelKanban\Actions\Rows;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Repositories\RowsRepository;

class DeleteRowAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'rowId' => ['required', 'integer', 'gt:0'],
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();
            $row = RowsRepository::findRow($this->rowId);
            RowsRepository::deleteRow($row->id);
            Log::createLog(
                Auth::user()->id,
                Log::TYPE_ROW_DELETED,
                'Deleted row [' . $row->name . '] from board [' . $row->board->name . ']',
                null,
                $row->id,
                'Xguard\LaravelKanban\Models\Row'
            );
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
