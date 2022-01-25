<?php

namespace Xguard\LaravelKanban\Repositories;

use Xguard\LaravelKanban\Models\Column;
use Illuminate\Database\Eloquent\Collection;

class ColumnsRepository
{
    public static function findColumn(int $id): ?Column
    {
        return Column::find($id);
    }

    public static function getColumns(int $row_id): Collection
    {
        return Column::where(Column::ROW_ID, $row_id)->orderBy(Column::INDEX)->get();
    }

    public static function updateColumnIndex(int $column_id, int $newIndex): Column
    {
        $column = Column::findOrFail($column_id);
        $column->update([Column::INDEX => $newIndex]);
        $column->refresh();
        return $column;
    }

    public static function deleteColumn(int $id): void
    {
        $column = Column::findOrFail($id);
        $column->delete();
    }

    public static function createColumn(array $payload): Column
    {
        return Column::create($payload);
    }

    public static function updateColumn(int $columnId, array $payload): Column
    {
        $columnToUpdate = Column::findOrFail($columnId);
        $columnToUpdate->update($payload);
        $columnToUpdate->refresh();
        return $columnToUpdate;
    }
}
