<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\TaskCard;

class TaskCardController extends Controller
{
    public function createTaskCards(Request $request)
    {
        $taskCards = $request->all();
        $maxIndex = TaskCard::where('column_id', $taskCards['columnId'])->max('index');

        try {
            foreach ($taskCards['employeesSelected'] as $taskCard) {
                $maxIndex++;
                TaskCard::create([
                    'index' => $maxIndex,
                    'employee_id' => $taskCard['employee']['id'],
                    'column_id' => $taskCards['columnId'],
                    'member_id' => $taskCard['id'],
                ]);
            }
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function getTaskCardsByColumn($id)
    {
        return TaskCard::where('column_id', $id)->with('employee')->get();
    }

    public function updateTaskCardIndexes(Request $request)
    {
        $taskCards = $request->all();
        $newIndex = 0;
        try {
            foreach ($taskCards as $taskCard) {
                $newIndex++;
                TaskCard::find($taskCard['id'])->update(['index' => $newIndex]);
            }
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function updateTaskCardColumnId($columnId, $taskCardId)
    {
        try {
            TaskCard::find($taskCardId)->update(['column_id' => $columnId]);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function deleteTaskCard($id)
    {
        try {
            $taskCard = TaskCard::find($id);
            $taskCard->delete();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

}
