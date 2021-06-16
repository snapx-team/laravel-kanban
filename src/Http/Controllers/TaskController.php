<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{


    public function getAllTasks()
    {
        return Task::all();
    }

    public function createBacklogTaskCards(Request $request)
    {
        $backlogTaskData = $request->all();

        $badge = Badge::firstOrCreate([
            'name' => $backlogTaskData['badge']['name'],
        ]);

        if ($backlogTaskData['associatedTask'] !== null){
            $group = $backlogTaskData['associatedTask']['group'];
        }
        else
            $group = 'g-' . (Task::max('id') + 1);

        $parsedDateTime = strtotime($backlogTaskData['deadline']);
        try {
            foreach ($backlogTaskData['selectedKanbans'] as $kanban) {
                Task::create([
                    'index' => null,
                    'reporter_id' => Auth::user()->id,
                    'name' => $backlogTaskData['name'],
                    'description' => $backlogTaskData['description'],
                    'deadline' => date('y-m-d h:m', strtotime($backlogTaskData['deadline'])),
                    'erp_employee_id' => $backlogTaskData['erpEmployee']['id'],
                    'erp_job_site_id' => $backlogTaskData['erpJobSite']['id'],
                    'badge_id' => $badge->id,
                    'column_id' => null,
                    'board_id' => $kanban['id'],
                    'group' => $group


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
