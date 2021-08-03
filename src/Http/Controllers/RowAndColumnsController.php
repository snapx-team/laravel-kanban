<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Row;

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
            } else {
                $newRow = Row::create([
                    'board_id' => $rowData['boardId'],
                    'name' => $rowData['name'],
                    'index' => $rowData['rowIndex'],
                ]);
                $rowId = $newRow->id;
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
                    $column = Column::find($currentColumnId);
                    $column->delete();
                }
            }

            /*  End : Delete columns */

            foreach ($rowData['columns'] as $key => $column) {
                if ($column['id'] !== null) {

                    Column::where('id', $column['id'])
                        ->update(['name' => $column['name'], 'index' => $key]);

                } else {

                    Column::create([
                        'row_id' => $rowId,
                        'name' => $column['name'],
                        'index' => $key,
                    ]);
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
        $column = Column::find($id);
        $column->delete();
    }

    public function deleteRow($id)
    {
        $column = Row::find($id);
        $column->delete();
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
