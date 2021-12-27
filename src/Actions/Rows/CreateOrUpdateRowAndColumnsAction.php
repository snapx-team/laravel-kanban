<?php

namespace Xguard\LaravelKanban\Actions\Rows;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Repositories\RowsRepository;
use Xguard\LaravelKanban\Repositories\ColumnsRepository;

class CreateOrUpdateRowAndColumnsAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'rowId' => ['nullable', 'integer', 'gt:0'],
            'name' => ['required', 'string'],
            'rowIndex' => ['required', 'integer', 'gte:0'],
            'boardId' => ['required', 'integer', 'gt:0'],
            'sentColumns' => ['present', 'array']
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
            if ($this->rowId !== null) {
                $updatedRow = RowsRepository::updateRow($this->rowId, ['name' => $this->name, 'index' => $this->rowIndex]);
            
                $rowId = $updatedRow->id;
                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_ROW_UPDATED,
                    'Updated row [' . $updatedRow->name . '] in board [' . $updatedRow->board->name . ']',
                    null,
                    $updatedRow->id,
                    'Xguard\LaravelKanban\Models\Row'
                );
            } else {
                $newRow = RowsRepository::createRow([
                    'board_id' => $this->boardId,
                    'name' => $this->name,
                    'index' => $this->rowIndex,
                ]);
                $rowId = $newRow->id;
                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_ROW_CREATED,
                    'Created row [' . $newRow->name . '] in board [' . $newRow->board->name . ']',
                    null,
                    $newRow->id,
                    'Xguard\LaravelKanban\Models\Row'
                );
            }

            /*  Start: Delete columns
                Code to compare new list of columns with existing list of columns
                If an existing column isn't included in the new list then it was selected to be deleted
            */

            $currentRow = RowsRepository::getRowWithColumns($rowId)->toArray();
            $currentColumns = $currentRow[0]['columns'];
            $sentColumns = $this->sentColumns;

            $currentColumnsId = array_map(function ($e) {
                return is_object($e) ? $e->id : $e['id'];
            }, $currentColumns);

            $sentColumnsId = array_map(function ($e) {
                return is_object($e) ? $e->id : $e['id'];
            }, $sentColumns);

            foreach ($currentColumnsId as $currentColumnId) {
                if (!in_array($currentColumnId, $sentColumnsId)) {
                    $column = ColumnsRepository::findColumn($currentColumnId);
                    ColumnsRepository::deleteColumn($currentColumnId);

                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_COLUMN_DELETED,
                        'Deleted column [' . $column->name . '] from row [' . $column->row->name . '] from board [' . $column->row->board->name . ']',
                        null,
                        $column->id,
                        'Xguard\LaravelKanban\Models\Column'
                    );
                }
            }

            /*  End : Delete columns */

            foreach ($this->sentColumns as $key => $column) {
                if ($column['id'] !== null) {
                    $newColumn = ColumnsRepository::updateColumn($column['id'], ['name' => $column['name'], 'index' => $key]);
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_COLUMN_UPDATED,
                        'Updated column  [' . $newColumn->name . '] in row [' . $newColumn->row->name . '] from board [' . $newColumn->row->board->name . ']',
                        null,
                        $newColumn->id,
                        'Xguard\LaravelKanban\Models\Column'
                    );
                } else {
                    $newColumn = ColumnsRepository::createColumn([
                        'row_id' => $rowId,
                        'name' => $column['name'],
                        'index' => $key,
                    ]);

                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_COLUMN_CREATED,
                        'Created column [' . $newColumn->name . '] in row [' . $newColumn->row->name . '] in board [' . $newColumn->row->board->name . ']',
                        null,
                        $newColumn->id,
                        'Xguard\LaravelKanban\Models\Column'
                    );
                }
            }

            return RowsRepository::getRowWithColumnsTaskCards($rowId);
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
