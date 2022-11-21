<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Actions\Tasks\UpdateTaskCardIndexesAction;
use Xguard\LaravelKanban\Enums\TaskStatuses;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Actions\Tasks\PlaceTaskAction;
use Xguard\LaravelKanban\Actions\Tasks\CreateTaskAction;
use Xguard\LaravelKanban\Actions\Tasks\PaginateBackLogTasksAction;
use Xguard\LaravelKanban\Actions\Tasks\RemoveTaskFromGroupAction;
use Xguard\LaravelKanban\Actions\Tasks\UpdateGroupAction;
use Xguard\LaravelKanban\Actions\Tasks\UpdateTaskAction;
use Xguard\LaravelKanban\Actions\Tasks\UpdateTaskColumnAndRowAction;
use Xguard\LaravelKanban\Actions\Tasks\UpdateTaskDescriptionAction;
use Xguard\LaravelKanban\Actions\Tasks\UpdateTaskStatusAction;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class TaskController extends Controller
{
    public function getBacklogTasks(Request $request)
    {
        return app(PaginateBackLogTasksAction::class)->fill($request->all())->run();
    }

    public function getRecentlyCreatedTasksByEmployee($employeeId)
    {
        return TasksRepository::getRecentlyCreatedTasksByEmployee($employeeId);
    }

    public function getAllTasks()
    {
        return Task::with(Task::BOARD_RELATION_NAME, Task::SHARED_TASK_DATA_RELATION_NAME)->take(5)->get();
    }

    public function getSomeTasks($searchTerm)
    {
        return Task::with(Task::BOARD_RELATION_NAME)->whereSearchText($searchTerm)->orderBy(Task::NAME, 'ASC')->take(10)->get();
    }

    public function getTaskData($id)
    {
        return Task::where(Task::ID, $id)->withTaskData()->first();
    }

    public function getRelatedTasks($id)
    {
        $relatedTasks = Task::findOrFail($id);
        return Task::where(Task::ID, '!=', $id)
            ->where(Task::SHARED_TASK_DATA_RELATION_ID, $relatedTasks->shared_task_data_id)
            ->withTaskData()
            ->get();
    }

    public function getRelatedTasksLessInfo($id)
    {
        $sharedTaskData = Task::findOrFail($id);
        return Task::where(Task::ID, '!=', $id)
            ->where(Task::SHARED_TASK_DATA_RELATION_ID, $sharedTaskData->shared_task_data_id)
            ->with(Task::BOARD_RELATION_NAME, Task::SHARED_TASK_DATA_RELATION_NAME)
            ->get();
    }

    public function createBacklogTaskCards(Request $request)
    {
        try {
            $taskFilesToUpload = $request->file('file');
            $taskCard = json_decode(file_get_contents($request->file('taskCardData')), true);
            app(CreateTaskAction::class)->fill([
                CreateTaskAction::ASSIGNED_TO => null,
                CreateTaskAction::ASSOCIATED_TASK => $taskCard['associatedTask'],
                CreateTaskAction::BADGE => $taskCard['badge'],
                CreateTaskAction::COLUMN_ID => null,
                CreateTaskAction::DEADLINE => $taskCard['deadline'],
                CreateTaskAction::DESCRIPTION => $taskCard['shared_task_data']['description'],
                CreateTaskAction::ERP_EMPLOYEES => $taskCard['shared_task_data']['erp_employees'],
                CreateTaskAction::ERP_CONTRACTS => $taskCard['shared_task_data']['erp_contracts'],
                CreateTaskAction::NAME => $taskCard['name'],
                CreateTaskAction::ROW_ID => null,
                CreateTaskAction::SELECTED_KANBANS => $taskCard['selectedKanbans'],
                CreateTaskAction::TIME_ESTIMATE => $taskCard['time_estimate'],
                CreateTaskAction::TASK_FILES => null,
                CreateTaskAction::FILES_TO_UPLOAD => $taskFilesToUpload,
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
            $taskFilesToUpload = $request->file('file');
            $taskCard = json_decode(file_get_contents($request->file('taskCardData')), true);
            $selectedKanban = [];
            $board = ['id' => $taskCard['boardId']];
            array_push($selectedKanban, $board);
            app(CreateTaskAction::class)->fill([
                CreateTaskAction::ASSIGNED_TO => $taskCard['assignedTo'],
                CreateTaskAction::ASSOCIATED_TASK => $taskCard['associatedTask'],
                CreateTaskAction::BADGE => $taskCard['badge'],
                CreateTaskAction::COLUMN_ID => $taskCard['selectedColumnId'],
                CreateTaskAction::DEADLINE => $taskCard['deadline'],
                CreateTaskAction::DESCRIPTION => $taskCard['shared_task_data']['description'],
                CreateTaskAction::ERP_EMPLOYEES => $taskCard['shared_task_data']['erp_employees'],
                CreateTaskAction::ERP_CONTRACTS => $taskCard['shared_task_data']['erp_contracts'],
                CreateTaskAction::NAME => $taskCard['name'],
                CreateTaskAction::ROW_ID => $taskCard['selectedRowId'],
                CreateTaskAction::SELECTED_KANBANS => $selectedKanban,
                CreateTaskAction::TIME_ESTIMATE => $taskCard['time_estimate'],
                CreateTaskAction::TASK_FILES => null,
                CreateTaskAction::FILES_TO_UPLOAD => $taskFilesToUpload,
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
        try {
            $taskFilesToUpload = $request->file('file');
            $taskCard = json_decode(file_get_contents($request->file('taskCardData')), true);
            app(UpdateTaskAction::class)->fill([
                UpdateTaskAction::ASSIGNED_TO => $taskCard['assigned_to'],
                UpdateTaskAction::BADGE => $taskCard['badge'],
                UpdateTaskAction::COLUMN_ID => $taskCard['column_id'],
                UpdateTaskAction::DEADLINE => $taskCard['deadline'],
                UpdateTaskAction::DESCRIPTION => $taskCard['shared_task_data']['description'],
                UpdateTaskAction::ERP_CONTRACTS => $taskCard['shared_task_data']['erp_contracts'],
                UpdateTaskAction::ERP_EMPLOYEES => $taskCard['shared_task_data']['erp_employees'],
                UpdateTaskAction::NAME => $taskCard['name'],
                UpdateTaskAction::ROW_ID => $taskCard['row_id'],
                UpdateTaskAction::STATUS => $taskCard['status'],
                UpdateTaskAction::TASK_ID => $taskCard['id'],
                UpdateTaskAction::SHARED_TASK_DATA_ID => $taskCard['shared_task_data_id'],
                UpdateTaskAction::TIME_ESTIMATE => $taskCard['time_estimate'],
                UpdateTaskAction::TASK_FILES => $taskCard['task_files'],
                UpdateTaskAction::FILES_TO_UPLOAD => $taskFilesToUpload,

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
        return Task::where(Task::COLUMN_ID, $id)->where(
            Task::STATUS,
            TaskStatuses::ACTIVE()->getValue()
        )->withTaskData()->orderBy(Task::INDEX)->get();
    }

    public function updateTaskCardIndexes(Request $request)
    {
        try {
            app(UpdateTaskCardIndexesAction::class)->fill([
                UpdateTaskCardIndexesAction::DATA => $request->all()
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'errors' => $e->getErrors()
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function updateTaskCardRowAndColumnId($columnId, $rowId, $taskCardId)
    {
        try {
            app(UpdateTaskColumnAndRowAction::class)->fill([
                UpdateTaskColumnAndRowAction::COLUMN_ID => $columnId,
                UpdateTaskColumnAndRowAction::ROW_ID => $rowId,
                UpdateTaskColumnAndRowAction::TASK_ID => $taskCardId,
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
                UpdateTaskDescriptionAction::CHECK_BOX_CONTENT => $descriptionData['checkboxContent'],
                UpdateTaskDescriptionAction::DESCRIPTION => $descriptionData['description'],
                UpdateTaskDescriptionAction::IS_CHECKED => $descriptionData['isChecked'] === "true",
                UpdateTaskDescriptionAction::TASK_ID => $descriptionData['id'],
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
                UpdateTaskStatusAction::TASK_ID => $taskCardId,
                UpdateTaskStatusAction::NEW_STATUS => $status
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function placeTask(Request $request)
    {
        $taskPlacementData = $request->all();
        try {
            app(PlaceTaskAction::class)->fill([
                PlaceTaskAction::TASK_ID => $taskPlacementData['taskId'],
                PlaceTaskAction::BOARD_ID => $taskPlacementData['boardId'],
                PlaceTaskAction::ROW_ID => $taskPlacementData['rowId'],
                PlaceTaskAction::COLUMN_ID => $taskPlacementData['columnId'],
            ])->run();
        } catch (\Exception $e) {
            return response([
                'errors' => $e->errors(),
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
                UpdateGroupAction::TASK_ID => $task_id,
                UpdateGroupAction::GROUP_ID => $group
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
                RemoveTaskFromGroupAction::TASK_ID => $id
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
