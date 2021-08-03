<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function getAllTasks()
    {
        return Task::with('board')->get();
    }

    public function getRelatedTasks($id)
    {
        $group = Task::where('id', $id)->first()->group;
        return Task::where('id', '!=', $id)->where('group', $group)->with('badge', 'row', 'column', 'board')
            ->with(['assignedTo.employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['erpEmployee' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['reporter' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['erpJobSite' => function ($q) {
                $q->select(['id', 'name']);
            }])->get();
    }

    public function getRelatedTasksLessInfo($id)
    {
        $group = Task::where('id', $id)->first()->group;
        return Task::where('id', '!=', $id)->where('group', $group)->with('board')->get();
    }

    public function createBacklogTaskCards(Request $request)
    {
        $rules = [
            'selectedKanbans' => 'array|min:1',
            'description' => 'required',
            'name' => 'required'
        ];

        $customMessages = [
            'selectedKanbans.min' => 'You need to select at least one board',
            'description.required' => 'Description is required',
            'name.required' => 'Name is required',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response([
                'success' => 'false',
                'message' => implode(', ', $validator->messages()->all()),
            ], 400);
        }

        $backlogTaskData = $request->all();

        try {
            $badge = Badge::firstOrCreate([
                'name' => count($request->input('badge')) > 0 ? $backlogTaskData['badge']['name'] : '--',
            ]);

            if ($backlogTaskData['associatedTask'] !== null) {
                $group = $backlogTaskData['associatedTask']['group'];
            } else {
                $group = 'g-' . (Task::max('id') + 1);
            }

            foreach ($backlogTaskData['selectedKanbans'] as $kanban) {
                $task = Task::create([
                    'index' => null,
                    'reporter_id' => Auth::user()->id,
                    'name' => $backlogTaskData['name'],
                    'description' => $backlogTaskData['description'],
                    'deadline' => $request->input('deadline')  !== null ? date('y-m-d h:m', strtotime($backlogTaskData['deadline'])) : null,
                    'erp_employee_id' => $request->input('erpEmployee') !== null ? $backlogTaskData['erpEmployee']['id'] : null,
                    'erp_job_site_id' => $request->input('erpJobSite') !== null ? $backlogTaskData['erpJobSite']['id'] : null,
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
        $rules = [
            'description' => 'required',
            'name' => 'required'
        ];

        $customMessages = [
            'description.required' => 'Description is required',
            'name.required' => 'Name is required',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response([
                'success' => 'false',
                'message' => implode(', ', $validator->messages()->all()),
            ], 400);
        }


        $taskCard = $request->all();

        try {
            $maxIndex = Task::where('column_id', $taskCard['columnId'])->max('index');

            $badge = Badge::firstOrCreate([
                'name' => count($request->input('badge')) > 0 ? $taskCard['badge']['name'] : '--',
            ]);

            if ($taskCard['associatedTask'] !== null) {
                $group = $taskCard['associatedTask']['group'];
            } else {
                $group = 'g-' . (Task::max('id') + 1);
            }

            $maxIndex++;
            $task = Task::create([
                'index' => $maxIndex,
                'reporter_id' => Auth::user()->id,
                'name' => $taskCard['name'],
                'description' => $taskCard['description'],
                'deadline' => $request->input('deadline')  !== null ? date('y-m-d h:m', strtotime($taskCard['deadline'])) : null,
                'erp_employee_id' => $request->input('erpEmployee') !== null ? $taskCard['erpEmployee']['id'] : null,
                'erp_job_site_id' => $request->input('erpJobSite') !== null ? $taskCard['erpJobSite']['id'] : null,
                'badge_id' => $badge->id,
                'column_id' => $taskCard['selectedColumnId'],
                'row_id' => $taskCard['selectedRowId'],
                'board_id' => $taskCard['boardId'],
                'group' => $group
            ]);

            if ($request->input('assignedTo') !== null) {
                $employeeArray = [];
                foreach ($taskCard['assignedTo'] as $employee) {
                    array_push($employeeArray, $employee['employee_id']);
                }
                $task->assignedTo()->sync($employeeArray);
            }

            Log::createLog($task->reporter_id, Log::TYPE_CARD_CREATED, 'Added new task', $task->badge_id, $task->board_id, $task->id, $task->erp_employee_id, $task->erp_job_site_id, null);
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

        try {
            $badge = Badge::firstOrCreate([
                'name' => count($request->input('badge')) > 0 ? $taskCard['badge']['name'] : '--'
            ]);

            if ($request->input('assigned_to') !== null) {
                $employeeArray = [];
                foreach ($taskCard['assigned_to'] as $employee) {
                    array_push($employeeArray, $employee['employee']['id']);
                }

                $task = Task::find($taskCard['id']);
                $task->assignedTo()->sync($employeeArray);
            }

            if ($taskCard['group'] !== null) {
                $group = $taskCard['group'];
            } else {
                $group = $task->group;
            }

            Task::where('id', $taskCard['id'])
                ->update([
                    'name' => $taskCard['name'],
                    'description' => $taskCard['description'],
                    'deadline' => $request->input('deadline')  !== null  ? date('y-m-d h:m', strtotime($taskCard['deadline'])) : null,
                    'erp_employee_id' => $request->input('erp_employee') !== null ? $taskCard['erp_employee']['id'] : null,
                    'erp_job_site_id' => $request->input('erp_job_site') !== null ? $taskCard['erp_job_site']['id'] : null,
                    'badge_id' => $badge->id,
                    'column_id' => $taskCard['column_id'] ?? null,
                    'row_id' => $taskCard['row_id'] ?? null,
                    'group' => $group
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
        return Task::where('column_id', $id)->with('badge', 'row', 'column', 'board')
            ->with(['assignedTo.employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['erpEmployee' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['reporter' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['erpJobSite' => function ($q) {
                $q->select(['id', 'name']);
            }])->orderBy('index')->get();
    }

    public function updateTaskCardIndexes(Request $request)
    {
        $taskCards = $request->all();
        $newIndex = 0;
        try {
            foreach ($taskCards as $taskCard) {
                Task::find($taskCard['id'])->update(['index' => $newIndex]);
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

    public function updateTaskCardRowAndColumnId($columnId, $rowId, $taskCardId)
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

    public function setStatus($taskCardId, $status)
    {
        try {
            $taskCard = Task::find($taskCardId);
            $taskCard->update([
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function assignTaskToBoard($task_id, $row_id, $column_id)
    {
        try {
            $taskCard = Task::find($task_id);
            $taskCard->update([
                'row_id' => $row_id,
                'column_id' => $column_id
            ]);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function updateGroup($task_id, $group)
    {
        try {
            $taskCard = Task::find($task_id);
            $taskCard->update([
                'group' => $group,
            ]);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }
}
