<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\HTTP\Response;
use Xguard\LaravelKanban\Actions\Columns\DeleteColumnAction;
use Xguard\LaravelKanban\Actions\Columns\UpdateColumIndexesAction;
use Xguard\LaravelKanban\Actions\Rows\CreateOrUpdateRowAndColumnsAction;
use Xguard\LaravelKanban\Actions\Rows\DeleteRowAction;
use Xguard\LaravelKanban\Actions\Rows\UpdateRowIndexesAction;
use Xguard\LaravelKanban\Repositories\ColumnsRepository;
use Xguard\LaravelKanban\Repositories\RowsRepository;

class RowAndColumnsController extends Controller
{
    public function createOrUpdateRowAndColumns(Request $request)
    {
        $rowData = $request->all();
        try {
            app(CreateOrUpdateRowAndColumnsAction::class)->fill([
                'rowId' => $rowData['rowId'],
                'name' => $rowData['name'],
                'rowIndex' => $rowData['rowIndex'],
                'boardId' => $rowData['boardId'],
                'sentColumns' => $rowData['columns']
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function updateRowIndexes(Request $request): Response
    {
        $rows = $request->all();
        try {
            app(UpdateRowIndexesAction::class)->fill(['rows' => $rows])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function updateColumnIndexes(Request $request): Response
    {
        $columns = $request->all();
        try {
            app(UpdateColumIndexesAction::class)->fill(['columns' => $columns])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }


    public function deleteColumn($id): void
    {
        app(DeleteColumnAction::class)->fill(['columnId' => $id])->run();
    }

    public function deleteRow($id): void
    {
        app(DeleteRowAction::class)->fill(['rowId' => $id])->run();
    }

    public function getRows($board_id): Collection
    {
        return RowsRepository::getRows($board_id);
    }

    public function getColumns($row_id): Collection
    {
        return ColumnsRepository::getColumns($row_id);
    }
}
