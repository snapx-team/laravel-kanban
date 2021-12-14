<?php

namespace Xguard\LaravelKanban\Repositories;

use Xguard\LaravelKanban\Models\Row;
use Illuminate\Database\Eloquent\Collection;

class RowsRepository
{
    public static function findRow(int $id): ?Row
    {
        return Row::find($id);
    }

    public static function getRows(int $boardId): Collection
    {
        return Row::where('board_id', $boardId)->get();
    }

    public static function getRowWithColumns(int $rowId): Collection
    {
        return Row::where('id', $rowId)->with('columns')->get();
    }

    public static function getRowWithColumnsTaskCards(int $rowId): Collection
    {
        return Row::where('id', $rowId)->with('columns.taskCards')->get();
    }

    public static function updateRowIndex(int $rowId, int $newIndex): Row
    {
        $row = Row::findOrFail($rowId);
        $row->update(['index' => $newIndex]);
        $row->refresh();
        return $row;
    }

    public static function deleteRow(int $id): void
    {
        $row = Row::findOrFail($id);
        $row->delete();
    }

    public static function createRow(array $payload): Row
    {
        return Row::create($payload);
    }

    public static function updateRow(int $rowId, array $payload): Row
    {
        $rowToUpdate = Row::findOrFail($rowId);
        $rowToUpdate->update($payload);
        $rowToUpdate->refresh();
        return $rowToUpdate;
    }
}
