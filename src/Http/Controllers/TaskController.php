<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Http\Helper\CheckHasAccessToBoardWithTaskId;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function getAllTasks()
    {
        return Task::with('board', 'sharedTaskData')->get();
    }

    public function getTaskData($id)
    {
        return Task::where('id', $id)->with('badge', 'row', 'column', 'board', 'sharedTaskData')
            ->with(['assignedTo.employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['erpEmployee' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['reporter' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['erpContract' => function ($q) {
                $q->select(['id', 'contract_identifier']);
            }])->first();
    }

    public function getRelatedTasks($id)
    {
        $relatedTasks = Task::where('id', $id)->first()->shared_task_data_id;

        return Task::where('id', '!=', $id)->where('shared_task_data_id', $relatedTasks)->with('badge', 'row', 'column', 'board', 'sharedTaskData')
            ->with(['assignedTo.employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['erpEmployee' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['reporter' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['erpContract' => function ($q) {
                $q->select(['id', 'contract_identifier']);
            }])->get();
    }

    public function getRelatedTasksLessInfo($id)
    {
        $sharedTaskData = Task::where('id', $id)->first()->shared_task_data_id;
        return Task::where('id', '!=', $id)->where('shared_task_data_id', $sharedTaskData)->with('board', 'sharedTaskData')->get();
    }


    public function createBacklogTaskCards(Request $request)
    {
        $rules = [
            'selectedKanbans' => 'array|min:1',
            'name' => 'required'
        ];

        $customMessages = [
            'selectedKanbans.min' => 'You need to select at least one board',
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
                $sharedTaskDataId = $backlogTaskData['associatedTask']['shared_task_data_id'];
            } else {
                $sharedTaskData = SharedTaskData::create(['description' => $backlogTaskData['description']]);
                $sharedTaskDataId = $sharedTaskData->id;
            }

            foreach ($backlogTaskData['selectedKanbans'] as $kanban) {
                $task = Task::with('board')->create([
                    'index' => null,
                    'reporter_id' => Auth::user()->id,
                    'name' => $backlogTaskData['name'],
                    'deadline' => $request->input('deadline') !== null ? date('y-m-d h:m', strtotime($backlogTaskData['deadline'])) : null,
                    'erp_employee_id' => $request->input('erpEmployee') !== null ? $backlogTaskData['erpEmployee']['id'] : null,
                    'erp_contract_id' => $request->input('erpContract') !== null ? $backlogTaskData['erpContract']['id'] : null,
                    'badge_id' => $badge->id,
                    'column_id' => null,
                    'board_id' => $kanban['id'],
                    'shared_task_data_id' => $sharedTaskDataId
                ]);

                if ($badge->wasRecentlyCreated) {
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_BADGE_CREATED,
                        "The badge [" . $badge->name . "] was created",
                        $badge->id,
                        null,
                        $task->id,
                        null
                    );
                }

                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_CREATED,
                    'Created new backlog task [' . $task->task_simple_name . '] in board [' . $task->board->name . '>',
                    $task->badge_id,
                    $task->board_id,
                    $task->id,
                    null
                );
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
            'name' => 'required'
        ];

        $customMessages = [
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
            $maxIndex = Task::where('column_id', $taskCard['columnId'])->where('status', 'active')->max('index');

            $badge = Badge::firstOrCreate([
                'name' => count($request->input('badge')) > 0 ? $taskCard['badge']['name'] : '--',
            ]);

            if ($taskCard['associatedTask'] !== null) {
                $sharedTaskDataId = $taskCard['associatedTask']['shared_task_data_id'];
            } else {
                $sharedTaskData = SharedTaskData::create(['description' => $taskCard['description']]);
                $sharedTaskDataId = $sharedTaskData->id;
            }

            $maxIndex++;
            $task = Task::with('board')->create([
                'index' => $maxIndex,
                'reporter_id' => Auth::user()->id,
                'name' => $taskCard['name'],
                'deadline' => $request->input('deadline') !== null ? date('y-m-d h:m', strtotime($taskCard['deadline'])) : null,
                'erp_employee_id' => $request->input('erpEmployee') !== null ? $taskCard['erpEmployee']['id'] : null,
                'erp_contract_id' => $request->input('erpContract') !== null ? $taskCard['erpContract']['id'] : null,
                'badge_id' => $badge->id,
                'column_id' => $taskCard['selectedColumnId'],
                'row_id' => $taskCard['selectedRowId'],
                'board_id' => $taskCard['boardId'],
                'shared_task_data_id' => $sharedTaskDataId
            ]);

            if ($badge->wasRecentlyCreated) {
                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_BADGE_CREATED,
                    "The badge [" . $badge->name . "] was created",
                    $badge->id,
                    null,
                    $task->id,
                    null
                );
            }

            if ($request->input('assignedTo') !== null) {
                $employeeArray = [];
                foreach ($taskCard['assignedTo'] as $employee) {
                    array_push($employeeArray, $employee['employee_id']);
                }
                $task->assignedTo()->sync($employeeArray);
            }

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_CREATED,
                'Created new task [' . $task->task_simple_name . '] on board [' . $task->board->name . ']',
                $task->badge_id,
                $task->board_id,
                $task->id,
                null
            );
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

        $hasBoardAccess = (new CheckHasAccessToBoardWithTaskId())->returnBool($taskCard['id']);

        if ($hasBoardAccess) {
            try {
                $prevTask = Task::with('board')->find($taskCard['id'])->toArray();
                $prevGroup = Task::find($taskCard['id'])->shared_task_data_id;


                if ($request->input('assigned_to') !== null) {
                    $employeeArray = [];
                    foreach ($taskCard['assigned_to'] as $employee) {
                        array_push($employeeArray, $employee['employee']['id']);
                    }

                    $task = Task::find($taskCard['id']);

                    $assignedToResponse = $task->assignedTo()->sync($employeeArray);

                    foreach ($assignedToResponse['attached'] as $employeeId) {
                        $employee = Employee::with('user')->find($employeeId);
                        Log::createLog(
                            Auth::user()->id,
                            Log::TYPE_CARD_ASSIGNED_TO_USER,
                            'User ' . $employee['employee']['user']['full_name'] . ' has been assigned to task [' . $task->task_simple_name . ']',
                            $taskCard['badge_id'],
                            $taskCard['board_id'],
                            $taskCard['id'],
                            $employee['id']
                        );
                    }

                    foreach ($assignedToResponse['detached'] as $employeeId) {
                        $employee = Employee::with('user')->find($employeeId);
                        Log::createLog(
                            Auth::user()->id,
                            Log::TYPE_CARD_UNASSIGNED_TO_USER,
                            'User ' . $employee['employee']['user']['full_name'] . ' has been removed from task [' . $task->task_simple_name . ']',
                            $taskCard['badge_id'],
                            $taskCard['board_id'],
                            $taskCard['id'],
                            $employee['id']
                        );
                    }

                    $badge = Badge::firstOrCreate([
                        'name' => count($request->input('badge')) > 0 ? $taskCard['badge']['name'] : '--'
                    ]);

                    if ($badge->wasRecentlyCreated) {
                        Log::createLog(
                            Auth::user()->id,
                            Log::TYPE_BADGE_CREATED,
                            "The badge [" . $badge->name . "] was created",
                            $badge->id,
                            $task->board_id,
                            $task->id,
                            null
                        );
                    }
                }

                $sharedTaskDataId = $taskCard['shared_task_data_id'];

                if ($sharedTaskDataId === $task->shared_task_data_id) {
                    // update description if group hasn't changed
                    SharedTaskData::where('id', $sharedTaskDataId)->update(['description' => $taskCard['shared_task_data']['description']]);
                } else {
                    // delete previous group if no other tasks point to it
                    $tasksWithSharedTaskData = Task::where('shared_task_data_id', $prevGroup)->count();
                    if ($tasksWithSharedTaskData === 0) {
                        SharedTaskData::where('id', $prevGroup)->delete();
                    }
                }

                $dt = new DateTime($taskCard['deadline']);
                $dt->setTimezone(new DateTimeZone('America/New_York'));
                $dt->format('y-m-d h:m');

                $task = Task::find($taskCard['id']);
                $task->update([
                    'name' => $taskCard['name'],
                    'status' => $taskCard['status'],
                    'deadline' => $request->input('deadline') !== null ? $dt : null,
                    'erp_employee_id' => $request->input('erp_employee') !== null ? $taskCard['erp_employee']['id'] : null,
                    'erp_contract_id' => $request->input('erp_contract') !== null ? $taskCard['erp_contract']['id'] : null,
                    'badge_id' => $badge->id,
                    'column_id' => $taskCard['column_id'] ?? $task->column_id,
                    'row_id' => $taskCard['row_id'] ?? $task->row_id,
                    'shared_task_data_id' => $sharedTaskDataId
                ]);

                $task = Task::find($taskCard['id'])->toArray();


                // logic to log what was changed during update

                $ignoreColumns = array_flip(['created_at', 'updated_at', 'hours_to_deadline', 'task_simple_name', 'board']);
                $keys = array_filter(array_keys($task), function ($key) use ($ignoreColumns) {
                    return !array_key_exists($key, $ignoreColumns);
                });
                $currentData = [];
                $previousData = [];
                foreach ($keys as $key) {
                    $currentData[$key] = $task[$key];
                    $previousData[$key] = $prevTask[$key];
                }
                $difference = array_diff($previousData, $currentData);

                // end logic to log what was changed during update

                if (count($difference) > 0) {
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_CARD_UPDATED,
                        "[" . implode("','", array_keys($difference)) . "] was updated.",
                        $task['badge_id'],
                        $task['board_id'],
                        $task['id'],
                        null
                    );
                }
            } catch (\Exception $e) {
                return response([
                    'success' => 'false',
                    'message' => $e->getMessage(),
                ], 400);
            }
        } else {
            return response([
                'success' => 'false',
                'message' => 'You don\'t have access to this board',
            ], 400);
        }

        return response(['success' => 'true'], 200);
    }

    public function getTaskCardsByColumn($id)
    {
        return Task::where('column_id', $id)
            ->where('status', 'active')
            ->with('badge', 'row', 'column', 'board', 'sharedTaskData')
            ->with(['assignedTo.employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['erpEmployee' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['reporter' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['erpContract' => function ($q) {
                $q->select(['id', 'contract_identifier']);
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
            $task = Task::with('row', 'column', 'board')->get()->find($taskCardId);
            $prevRow = $task->row->name;
            $prevColumn = $task->column->name;
            $task->update(['column_id' => $columnId, 'row_id' => $rowId]);
            $task = Task::with('row', 'column', 'board')->get()->find($taskCardId);
            $row = $task->row->name;
            $column = $task->column->name;

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_MOVED,
                'Task [' . $task->task_simple_name . '] changed from [' . $prevRow . ':' . $prevColumn . '] to [' . $row . ':' . $column . ']',
                null,
                $task->board_id,
                $task->id,
                null
            );
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

        $hasBoardAccess = (new CheckHasAccessToBoardWithTaskId())->returnBool($descriptionData['id']);

        if ($hasBoardAccess) {
            try {
                $taskCard = Task::find($descriptionData['id']);
                SharedTaskData::where('id', $taskCard->shared_task_data_id)->update(['description' => $descriptionData['description']]);

                if ($descriptionData['isChecked'] === "true") {
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_CARD_CHECKLIST_ITEM_CHECKED,
                        'Checked -> ' . $descriptionData['checkboxContent'],
                        $taskCard->badge_id,
                        $taskCard->board_id,
                        $taskCard->id,
                        null
                    );
                } else {
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_CARD_CHECKLIST_ITEM_UNCHECKED,
                        'Unchecked -> ' . $descriptionData['checkboxContent'],
                        $taskCard->badge_id,
                        $taskCard->board_id,
                        $taskCard->id,
                        null
                    );
                }
            } catch (\Exception $e) {
                return response([
                    'success' => 'false',
                    'message' => $e->getMessage(),
                    'data' => $descriptionData,
                ], 400);
            }
        } else {
            return response([
                'success' => 'false',
                'message' => 'You don\'t have access to this board',
            ], 400);
        }

        return response(['success' => 'true'], 200);
    }

    public function setStatus($taskCardId, $status)
    {
        try {
            $task = Task::with('board')->get()->find($taskCardId);
            if ($status === 'active') {
                $task->update([
                    'status' => $status,
                ]);
            } else {
                $task->update([
                    'status' => $status,
                    'row_id' => null,
                    'column_id' => null,
                    'index' => null
                ]);
            }

            if ($status === 'completed') {
                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_COMPLETED,
                    'Task [' . $task->task_simple_name . '] in board [' . $task->board->name . '] set to completed',
                    $task->badge_id,
                    $task->board_id,
                    $task->id,
                    null
                );
            } elseif ($status === 'cancelled') {
                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_CANCELED,
                    'Task [' . $task->task_simple_name . '] in board [' . $task->board->name . '] set to cancelled',
                    $task->badge_id,
                    $task->board_id,
                    $task->id,
                    null
                );
            } elseif ($status === 'active') {
                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_CANCELED,
                    'Task [' . $task->task_simple_name . '] in board [' . $task->board->name . '] set to active',
                    $task->badge_id,
                    $task->board_id,
                    $task->id,
                    null
                );
            }
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
            $task = Task::with('board')->get()->find($task_id);
            $task->update([
                'board_id' => $board_id,
                'row_id' => $row_id,
                'column_id' => $column_id
            ]);

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_ASSIGNED_TO_BOARD,
                'Task [' . $task->task_simple_name . '] assigned to board [' . $task->board->name . '] on [' . $task->row->name . ':' . $task->column->name . ']',
                $task->badge_id,
                $task->board_id,
                $task->id,
                null
            );
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
            $prevGroup = $taskCard->shared_task_data_id;


            $taskCard->update([
                'shared_task_data_id' => $group,
            ]);

            // delete previous group if no other tasks point to it
            $tasksWithSharedTaskData = Task::where('shared_task_data_id', $prevGroup)->count();
            if ($tasksWithSharedTaskData === 0) {
                SharedTaskData::where('id', $prevGroup)->delete();
            }

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_ASSIGNED_GROUP,
                'Task [' . $taskCard->task_simple_name . '] changed group',
                $taskCard->badge_id,
                $taskCard->board_id,
                $taskCard->id,
                null
            );
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
            $taskCard = Task::with('sharedTaskData')->find($id);

            $sharedTaskData = SharedTaskData::create(['description' => $taskCard['sharedTaskData']['description']]);

            $taskCard->update([
                'shared_task_data_id' => $sharedTaskData->id,
            ]);

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_ASSIGNED_GROUP,
                'Task [' . $taskCard->task_simple_name . '] was removed from group',
                null,
                $taskCard->board->id,
                $taskCard->id,
                null
            );
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

}
