<?php

namespace Xguard\LaravelKanban\Actions\Columns;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Repositories\ColumnsRepository;

class DeleteColumnAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'columnId' => ['required', 'integer', 'gt:0'],
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
            $column = ColumnsRepository::findColumn($this->columnId);
            ColumnsRepository::deleteColumn($column->id);
            Log::createLog(
                Auth::user()->id,
                Log::TYPE_COLUMN_DELETED,
                'Deleted  column [' . $column->name . '] from row [' . $column->row->name . '] from board [' . $column->row->board->name . ']',
                null,
                $column->id,
                'Xguard\LaravelKanban\Models\Column'
            );
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
