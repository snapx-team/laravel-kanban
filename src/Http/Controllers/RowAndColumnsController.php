<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Row;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;

class RowAndColumnsController extends Controller
{
    public function createOrUpdateRowAndColumns(Request $request)
    {
        $rowData = $request->all();
        try {
            if ($rowData['rowId'] !== null) {
                $updatedRow = Row::where('id', $rowData['rowId'])
                    ->update(['name' => $rowData['name'], 'index' => $rowData['rowIndex']]);
                $rowId = $rowData['rowId'];
                $newRow = Row::with('board')->get()->find($rowId);

                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_ROW_UPDATED,
                    'Updated row [' . $newRow->name . '] in board [' . $newRow->board->name . ']',
                    null, 
                    $newRow->id,
                    'Xguard\LaravelKanban\Models\Row'
                );
            } else {
                $newRow = Row::with('board')->create([
                    'board_id' => $rowData['boardId'],
                    'name' => $rowData['name'],
                    'index' => $rowData['rowIndex'],
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

            $currentRow = Row::where('id', $rowId)->with('columns')->get()->toArray();
            $currentColumns = $currentRow[0]['columns'];
            $sentColumns = $rowData['columns'];

            $currentColumnsId = array_map(function ($e) {
                return is_object($e) ? $e->id : $e['id'];
            }, $currentColumns);

            $sentColumnsId = array_map(function ($e) {
                return is_object($e) ? $e->id : $e['id'];
            }, $sentColumns);

            foreach ($currentColumnsId as $currentColumnId) {
                if (!in_array($currentColumnId, $sentColumnsId)) {
                    $column = Column::with('row.board')->get()->find($currentColumnId);
                    $column->delete();

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

            foreach ($rowData['columns'] as $key => $column) {
                if ($column['id'] !== null) {
                    $newColumn = Column::where('id', $column['id'])
                        ->update(['name' => $column['name'], 'index' => $key]);
                    $newCol = Column::with('row.board')->get()->find($column['id']);

                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_COLUMN_UPDATED,
                        'Updated column  [' . $newCol->name . '] in row [' . $newCol->row->name . '] from board [' . $newCol->row->board->name . ']',
                        null,
                        $newCol->id,
                        'Xguard\LaravelKanban\Models\Column'
                    );
                } else {
                    $newColumn = Column::with('row.board')->create([
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

            return Row::where('id', $rowId)->with('columns.taskCards')->get();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function updateRowIndexes(Request $request)
    {
        $rows = $request->all();
        $newIndex = 0;
        try {
            foreach ($rows as $row) {
                Row::find($row['id'])->update(['index' => $newIndex]);
                $newIndex++;
            }
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function updateColumnIndexes(Request $request)
    {
        $columns = $request->all();
        $newIndex = 0;
        try {
            foreach ($columns as $column) {
                Column::find($column['id'])->update(['index' => $newIndex]);
                $newIndex++;
            }
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }


    public function deleteColumn($id)
    {
        $column = Column::with('row.board')->get()->find($id);
        $column->delete();
        Log::createLog(
            Auth::user()->id,
            Log::TYPE_COLUMN_DELETED,
            'Deleted  column [' . $column->name . '] from row [' . $column->row->name . '] from board [' . $column->row->board->name . ']',
            null,
            $column->id,
            'Xguard\LaravelKanban\Models\Column'
        );
    }

    public function deleteRow($id)
    {
        $row = Row::with('board')->get()->find($id);
        $row->delete();
        Log::createLog(
            Auth::user()->id,
            Log::TYPE_ROW_DELETED,
            'Deleted row [' . $row->name . '] from board [' . $row->board->name . ']',
            null,
            $row->id,
            'Xguard\LaravelKanban\Models\Row'
        );
    }

    public function getRows($board_id)
    {
        return Row::where('board_id', $board_id)->get();
    }

    public function getColumns($row_id)
    {
        return Column::where('row_id', $row_id)->get();
    }
}
