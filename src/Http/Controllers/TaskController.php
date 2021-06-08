<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\Task;

class TaskController extends Controller
{
    public function createTaskCard(Request $request)
    {
        $taskCard = $request->all();

        $maxIndex = Task::where('column_id', $taskCard['columnId'])->max('index');

        try {
            $maxIndex++;
            Task::create([
                'index' => $maxIndex,
                'employee_id' => $taskCard['employee']['id'],
                'column_id' => $taskCard['columnId'],
                'member_id' => $taskCard['id'],
            ]);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }


    public function createBacklogTaskCards(Request $request)
    {
        $backlogTaskData = $request->all();


        try {
            foreach ($backlogTaskData['selectedKanbans'] as $taskCard) {
                Task::create([
                    'index' => null,
                    'name' => $backlogTaskData['name'],

                    'description' => $backlogTaskData['description'],

                    'column_id' => null,
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
        return Task::where('column_id', $id)->with('employee.user')->get();
    }

    public function updateTaskCardIndexes(Request $request)
    {
        $taskCards = $request->all();
        $newIndex = 0;
        try {
            foreach ($taskCards as $taskCard) {
                $newIndex++;
                Task::find($taskCard['id'])->update(['index' => $newIndex]);
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
            Task::find($taskCardId)->update(['column_id' => $columnId]);
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
            $taskCard = Task::find($id);
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
