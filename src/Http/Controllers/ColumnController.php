<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\Column;

class ColumnController extends Controller
{
    public function createOrUpdateColumns(Request $request)
    {
        $columns = $request->all();

        try {
            Column::where('row_id', $columns['rowId'])->delete();
            foreach ($columns['ranges'] as $range) {
                Column::create([
                    'row_id' => $columns['rowId'],
                    'name' => $range['name'],
                    'shift_start' => $range['startTime'],
                    'shift_end' => $range['endTime'],
                ]);
            }
            return Column::where('row_id', $columns['rowId'])->with('taskCards')->get();

        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
