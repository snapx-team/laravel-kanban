<?php

namespace Xguard\LaravelKanban\Actions\Rows;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Repositories\RowsRepository;

class DeleteRowAction extends Action
{
    const ROW_ID = 'rowId';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::ROW_ID => ['required', 'integer', 'gt:0'],
        ];
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();
            $row = RowsRepository::findRow($this->rowId);
            RowsRepository::deleteRow($row->id);
            $rows = RowsRepository::getRows($row->board_id)->toArray();
            app(UpdateRowIndexesAction::class)->fill([UpdateRowIndexesAction::ROWS => $rows])->run();

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_ROW_DELETED,
                'Deleted row [' . $row->name . '] from board [' . $row->board->name . ']',
                null,
                $row->id,
                LoggableTypes::ROW()->getValue()
            );
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
