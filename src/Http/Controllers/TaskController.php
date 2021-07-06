<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{


    public function getAllTasks()
    {
        return Task::all();
    }

    public function getRelatedTasks($id)
    {
        $group = Task::where('id', $id)->first()->group;
        return Task::where('id', '!=' , $id)->where('group', $group)->with('badge','row', 'column')
        ->with(['assignedTo.user' => function($q){
            $q->select(['id','first_name','last_name']);
        }])
        ->with(['erpEmployee' => function($q){
            $q->select(['id','first_name','last_name']);
        }])
        ->with(['reporter' => function($q){
            $q->select(['id','first_name','last_name']);
        }])
        ->with(['erpJobSite' => function($q){
            $q->select(['id','name']);
        }])->get();
    }


    public function createBacklogTaskCards(Request $request)
    {
        $backlogTaskData = $request->all();

        $badge = Badge::firstOrCreate([
            'name' => $backlogTaskData['badge']['name'],
        ]);

        if ($backlogTaskData['associatedTask'] !== null) {
            $group = $backlogTaskData['associatedTask']['group'];
        } else
            $group = 'g-' . (Task::max('id') + 1);

        try {
            foreach ($backlogTaskData['selectedKanbans'] as $kanban) {
                $task = Task::create([
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

                Log::createLog($task->reporter_id, Log::TYPE_CARD_CREATED, 'Added new backlog task', $task->badge_id, $task->board_id, $task->id, $task->erp_employee_id, $task->erp_job_site_id, null);
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
            $task = Task::create([
                'index' => $maxIndex,
                'employee_id' => $taskCard['employee']['id'],
                'column_id' => $taskCard['columnId'],
                'member_id' => $taskCard['id'],
            ]);
            Log::createLog($task->reporter_id, Log::TYPE_CARD_CREATED, 'Added new task task', $task->badge_id, $task->board_id, $task->id, $task->erp_employee_id, $task->erp_job_site_id, null);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function updateTaskCard(Request $request)
    {
        $taskCard = $request->all();

        $badge = Badge::firstOrCreate([
            'name' => $taskCard['badge']['name'],
        ]);

        try {
            Task::where('id', $taskCard['id'])
                ->update([
                    'name' => $taskCard['name'],
                    'description' => $taskCard['description'],
                    'deadline' => date('y-m-d h:m', strtotime($taskCard['deadline'])),
                    'erp_employee_id' => $taskCard['erp_employee']['id'],
                    'erp_job_site_id' => $taskCard['erp_job_site']['id'],
                    'badge_id' => $badge->id,
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
        return Task::where('column_id', $id)->with('badge','row', 'column')
            ->with(['assignedTo.user' => function($q){
                $q->select(['id','first_name','last_name']);
            }])
            ->with(['erpEmployee' => function($q){
                $q->select(['id','first_name','last_name']);
            }])
            ->with(['reporter' => function($q){
                $q->select(['id','first_name','last_name']);
            }])
            ->with(['erpJobSite' => function($q){
                $q->select(['id','name']);
            }])->get();
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

    public function updateTaskCardColumnId($columnId, $rowId, $taskCardId)
    {
        try {
            Task::find($taskCardId)->update(['column_id' => $columnId, 'row_id' => $rowId]);
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


    public function updateDescription(Request $request)
    {
        $descriptionData = $request->all();

        try {
            $taskCard = Task::find($descriptionData['id']);
            $taskCard->update([
                'description' => $descriptionData['description'],
            ]);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'data' => $descriptionData,
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }
}
