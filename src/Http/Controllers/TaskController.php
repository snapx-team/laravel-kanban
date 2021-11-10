<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Actions\Tasks\AssignTaskToBoardAction;
use Xguard\LaravelKanban\Actions\Tasks\CreateTaskAction;
use Xguard\LaravelKanban\Actions\Tasks\PaginateBackLogTasksAction;
use Xguard\LaravelKanban\Actions\Tasks\RemoveTaskFromGroupAction;
use Xguard\LaravelKanban\Actions\Tasks\UpdateGroupAction;
use Xguard\LaravelKanban\Actions\Tasks\UpdateTaskAction;
use Xguard\LaravelKanban\Actions\Tasks\UpdateTaskColumnAndRowAction;
use Xguard\LaravelKanban\Actions\Tasks\UpdateTaskDescriptionAction;
use Xguard\LaravelKanban\Actions\Tasks\UpdateTaskStatusAction;

class TaskController extends Controller
{
    public function getBacklogTasks(Request $request)
    {
        return app(PaginateBackLogTasksAction::class)->fill($request->all())->run();
    }

    public function getAllTasks()
    {
        return Task::with('board', 'sharedTaskData')->take(5)->get();
    }

    public function getSomeTasks($searchTerm)
    {
        return Task::with('board')->whereSearchText($searchTerm)->take(10)->get();
    }

    public function getTaskData($id)
    {
        return Task::where('id', $id)->withTaskData()->first();
    }

    public function getRelatedTasks($id)
    {
        $relatedTasks = Task::findOrFail($id);
        return Task::where('id', '!=', $id)
            ->where('shared_task_data_id', $relatedTasks->shared_task_data_id)
            ->withTaskData()
            ->get();
    }

    public function getRelatedTasksLessInfo($id)
    {
        $sharedTaskData = Task::findOrFail($id);
        return Task::where('id', '!=', $id)
            ->where('shared_task_data_id', $sharedTaskData->shared_task_data_id)
            ->with('board', 'sharedTaskData')
            ->get();
    }

    public function createBacklogTaskCards(Request $request)
    {
        try {
            $backlogTaskData = $request->all();
            app(CreateTaskAction::class)->fill([
                'assinedTo' => null,
                'associatedTask' => $backlogTaskData['associatedTask'],
                'badge' => $backlogTaskData['badge'],
                'columnId' => null,
                'deadline' => $backlogTaskData['deadline'],
                'description' => $backlogTaskData['shared_task_data']['description'],
                'erpEmployees' => $backlogTaskData['shared_task_data']['erp_employees'],
                'erpContracts' => $backlogTaskData['shared_task_data']['erp_contracts'],
                'name' => $backlogTaskData['name'],
                'rowId' => null,
                'selectedKanbans' => $backlogTaskData['selectedKanbans'],
            ])->run();
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
        try {
            $taskCard = $request->all();
            $selectedKanban = [];
            $board = ['id'=>$taskCard['boardId']];
            array_push($selectedKanban, $board);
            app(CreateTaskAction::class)->fill([
                'assignedTo' => $taskCard['assignedTo'],
                'associatedTask' => $taskCard['associatedTask'],
                'badge' => $taskCard['badge'],
                'columnId' => $taskCard['selectedColumnId'],
                'deadline' => $taskCard['deadline'],
                'description' => $taskCard['shared_task_data']['description'],
                'erpEmployees' => $taskCard['shared_task_data']['erp_employees'],
                'erpContracts' => $taskCard['shared_task_data']['erp_contracts'],
                'name' => $taskCard['name'],
                'rowId' => $taskCard['selectedRowId'],
                'selectedKanbans' => $selectedKanban,
            ])->run();
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
            app(UpdateTaskAction::class)->fill([
                'assignedTo' => $taskCard['assigned_to'],
                'badge' => $taskCard['badge'],
                'columnId' => $taskCard['column_id'],
                'deadline' => $taskCard['deadline'],
                'description' => $taskCard['shared_task_data']['description'],
                'erpContracts' => $taskCard['shared_task_data']['erp_contracts'],
                'erpEmployees' => $taskCard['shared_task_data']['erp_employees'],
                'name' => $taskCard['name'],
                'rowId' => $taskCard['row_id'],
                'status' => $taskCard['status'],
                'taskId' => $taskCard['id'],
                'sharedTaskDataId' => $taskCard['shared_task_data_id'],
            ])->run();
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
        return Task::where('column_id', $id)->where('status', 'active')->withTaskData()->orderBy('index')->get();
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
            app(UpdateTaskColumnAndRowAction::class)->fill([
                'columnId' => $columnId,
                'rowId' => $rowId,
                'taskId' => $taskCardId,
            ])->run();
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
            app(UpdateTaskDescriptionAction::class)->fill([
                'checkBoxContent' => $descriptionData['checkboxContent'],
                'description' => $descriptionData['description'],
                'isChecked' => $descriptionData['isChecked'] === "true",
                'taskId' => $descriptionData['id'],
            ])->run();
            return response(['success' => 'true'], 200);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'data' => $descriptionData,
            ], 400);
        }
    }

    public function setStatus($taskCardId, $status)
    {
        try {
            app(UpdateTaskStatusAction::class)->fill([
                'taskId' => $taskCardId,
                'newStatus' => $status
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function assignTaskToBoard($task_id, $row_id, $column_id, $board_id)
    {
        try {
           app(AssignTaskToBoardAction::class)->fill([
            'taskId' => $task_id,
            'boardId' => $board_id,
            'rowId' => $row_id,
            'columnId' => $column_id, 
           ])->run();
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
            app(UpdateGroupAction::class)->fill([
                'taskId' => $task_id,
                'groupId' => $group
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function removeFromGroup($id)
    {
        try {
            app(RemoveTaskFromGroupAction::class)->fill([
                'taskId' => $id
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }
}
