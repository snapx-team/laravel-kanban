<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Http\Helper\CheckHasAccessToBoardWithTaskId;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Xguard\LaravelKanban\Models\TaskVersion;

class TaskController extends Controller
{

    public function getBacklogTasks(Request $request)
    {
        $filters = $request->all();


        $query = Task::with('badge', 'row', 'column', 'board', 'sharedTaskData')
            ->with(['reporter.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['assignedTo.employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['sharedTaskData' => function ($q) {
                $q->with(['erpContracts' => function ($q) {
                    $q->select(['contracts.id', 'contract_identifier']);
                }])->with(['erpEmployees' => function ($q) {
                    $q->select(['users.id', 'first_name', 'last_name']);
                }]);
            }])
            ->orderBy('deadline')
            ->whereDate('created_at', '>=', new DateTime($filters['filterStart']))
            ->whereDate('created_at', '<=', new DateTime($filters['filterEnd']))
            ->whereIn('status', $filters['filterStatus']);


        if (session('role') === 'employee') {
            $query->whereHas('board.members', function ($q) {
                $q->where('employee_id', session('employee_id'));
            });
        }


        if (!$filters['filterPlacedInBoard']) {
            $query->whereNull('column_id');
        }
        if (!$filters['filterNotPlacedInBoard']) {
            $query->whereNotNull('column_id');
        }

        if ($filters['filterText']) {
            $searchTerm = $filters['filterText'];

            $extractedLetters = preg_replace("/[^a-zA-Z]/", "", $searchTerm);
            $extractedNumbers = abs(filter_var($searchTerm, FILTER_SANITIZE_NUMBER_INT));

            if (preg_match("/[a-zA-Z]{1,3}-\d{1,7}/", $searchTerm)) {
                $query->whereHas('board', function ($q) use ($extractedLetters) {
                    $q->where('name', 'like', $extractedLetters . "%");
                })->where('id', 'like', $extractedNumbers . "%")
                    ->orWhere('name', 'like', "%{$searchTerm}%");
            } elseif ($extractedLetters != "") {
                $query
                    ->whereHas('board', function ($q) use ($extractedLetters) {
                        if ($extractedLetters != "") {
                            $q->where('name', 'like', $extractedLetters . "%");
                        }
                    })
                    ->orWhere('name', 'like', "%{$searchTerm}%");
            } elseif ($extractedNumbers != 0) {
                $query
                    ->where('id', 'like', $extractedNumbers . "%")
                    ->orWhere('name', 'like', "%{$searchTerm}%");
            }
        }

        if (!empty($filters['filterBadge'])) {
            $badges = $filters['filterBadge'];
            $badgeIds = array_column($badges, 'id');

            $query->whereHas('badge', function ($q) use ($badgeIds) {
                $q->whereIn('id', $badgeIds);
            });
        }

        if (!empty($filters['filterBoard'])) {
            $boards = $filters['filterBoard'];
            $boardIds = array_column($boards, 'id');

            $query->whereHas('board', function ($q) use ($boardIds) {
                $q->whereIn('id', $boardIds);
            });
        }


        if (!empty($filters['filterAssignedTo'])) {
            $assignedTo = $filters['filterAssignedTo'];
            $assignedToIds = array_column($assignedTo, 'id');

            $query->whereHas('assignedTo', function ($q) use ($assignedToIds) {
                $q->whereIn('employee_id', $assignedToIds);
            });
        }

        if (!empty($filters['filterReporter'])) {
            $reporters = $filters['filterReporter'];
            $reporterIds = array_column($reporters, 'id');

            $query->whereIn('reporter_id', $reporterIds);
        }

        return $query->paginate(15);
    }

    public function getAllTasks()
    {
        return Task::with('board', 'sharedTaskData')->take(5)->get();
    }

    public function getSomeTasks($searchTerm)
    {

        $query = Task::with('board');

        $extractedLetters = preg_replace("/[^a-zA-Z]/", "", $searchTerm);
        $extractedNumbers = abs(filter_var($searchTerm, FILTER_SANITIZE_NUMBER_INT));

        if (preg_match("/[a-zA-Z]{1,3}-\d{1,7}/", $searchTerm)) {
            $query->whereHas('board', function ($q) use ($extractedLetters) {
                $q->where('name', 'like', $extractedLetters . "%");
            })->where('id', 'like', $extractedNumbers . "%")
                ->orWhere('name', 'like', "%{$searchTerm}%");
        } elseif ($extractedLetters != "") {
            $query
                ->whereHas('board', function ($q) use ($extractedLetters) {
                    if ($extractedLetters != "") {
                        $q->where('name', 'like', $extractedLetters . "%");
                    }
                })
                ->orWhere('name', 'like', "%{$searchTerm}%");
        } elseif ($extractedNumbers != 0) {
            $query
                ->where('id', 'like', $extractedNumbers . "%")
                ->orWhere('name', 'like', "%{$searchTerm}%");
        }

        return $query->take(10)->get();
    }

    public function getTaskData($id)
    {
        return Task::where('id', $id)->with('badge', 'row', 'column', 'board', 'sharedTaskData')
            ->with(['assignedTo.employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['reporter.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['sharedTaskData' => function ($q) {
                $q->with(['erpContracts' => function ($q) {
                    $q->select(['contracts.id', 'contract_identifier']);
                }])->with(['erpEmployees' => function ($q) {
                    $q->select(['users.id', 'first_name', 'last_name']);
                }]);
            }])->first();
    }

    public function getRelatedTasks($id)
    {
        $relatedTasks = Task::where('id', $id)->first()->shared_task_data_id;

        return Task::where('id', '!=', $id)->where('shared_task_data_id', $relatedTasks)->with('badge', 'row', 'column', 'board', 'sharedTaskData')
            ->with(['assignedTo.employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['reporter.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['sharedTaskData' => function ($q) {
                $q->with(['erpContracts' => function ($q) {
                    $q->select(['contracts.id', 'contract_identifier']);
                }])->with(['erpEmployees' => function ($q) {
                    $q->select(['users.id', 'first_name', 'last_name']);
                }]);
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

                $erpEmployeesArray = [];
                $erpContractsArray = [];

                foreach ($backlogTaskData['shared_task_data']['erp_employees'] as $erpEmployee) {
                    $erpEmployeesArray[$erpEmployee['id']] = ['shareable_type' => 'user'];
                }


                foreach ($backlogTaskData['shared_task_data']['erp_contracts'] as $erpContract) {
                    $erpContractsArray[$erpContract['id']] = ['shareable_type' => 'contract'];
                }

                $sharedTaskData->erpContracts()->sync($erpContractsArray);
                $sharedTaskData->erpEmployees()->sync($erpEmployeesArray);
            }

            foreach ($backlogTaskData['selectedKanbans'] as $kanban) {
                $task = Task::with('board')->create([
                    'index' => null,
                    'reporter_id' => session('employee_id'),
                    'name' => $backlogTaskData['name'],
                    'deadline' => $request->input('deadline') !== null ? date('y-m-d h:m', strtotime($backlogTaskData['deadline'])) : null,
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
                        null,
                        $badge->id,
                        'Xguard\LaravelKanban\Models\Badge'
                    );
                }

                $log = Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_CREATED,
                    'Created new backlog task [' . $task->task_simple_name . '] in board [' . $task->board->name . '>',
                    null,
                    $task->id,
                    'Xguard\LaravelKanban\Models\Task'
                );

                TaskVersion::create([
                    "index" => $task->index,
                    "name" => $task->name,
                    "deadline" => $task->deadline,
                    "shared_task_data_id" =>$task->shared_task_data_id,
                    "reporter_id" => $task->reporter_id,
                    "column_id" => $task->column_id,
                    "row_id" => $task->row_id,
                    "board_id" => $task->board_id,
                    "badge_id" => $task->badge_id,
                    "status" => $task->status ? $task->status : 'active',
                    "task_id" => $task->id,
                    "log_id" => $log->id
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

                $erpEmployeesArray = [];
                $erpContractsArray = [];

                foreach ($taskCard['shared_task_data']['erp_employees'] as $erpEmployee) {
                    $erpEmployeesArray[$erpEmployee['id']] = ['shareable_type' => 'user'];
                }


                foreach ($taskCard['shared_task_data']['erp_contracts'] as $erpContract) {
                    $erpContractsArray[$erpContract['id']] = ['shareable_type' => 'contract'];
                }

                $sharedTaskData->erpContracts()->sync($erpContractsArray);
                $sharedTaskData->erpEmployees()->sync($erpEmployeesArray);

                $sharedTaskDataId = $sharedTaskData->id;
            }

            $maxIndex++;
            $task = Task::with('board')->create([
                'index' => $maxIndex,
                'reporter_id' => session('employee_id'),
                'name' => $taskCard['name'],
                'deadline' => $request->input('deadline') !== null ? date('y-m-d h:m', strtotime($taskCard['deadline'])) : null,
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
                    null,
                    $badge->id,
                    'Xguard\LaravelKanban\Models\Badge'
                );
            }

            if ($request->input('assignedTo') !== null) {
                $employeeArray = [];
                foreach ($taskCard['assignedTo'] as $employee) {
                    array_push($employeeArray, $employee['employee_id']);
                }
                $task->assignedTo()->sync($employeeArray);
            }

            $log = Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_CREATED,
                'Created new task [' . $task->task_simple_name . '] on board [' . $task->board->name . ']',
                null,
                $task->id,
                'Xguard\LaravelKanban\Models\Task'
            );

            TaskVersion::create([
                "index" => $task->index,
                "name" => $task->name,
                "deadline" => $task->deadline,
                "shared_task_data_id" =>$task->shared_task_data_id,
                "reporter_id" => $task->reporter_id,
                "column_id" => $task->column_id,
                "row_id" => $task->row_id,
                "board_id" => $task->board_id,
                "badge_id" => $task->badge_id,
                "status" => $task->status ? $task->status : 'active',
                "task_id" => $task->id,
                "log_id" => $log->id
            ]);
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
                        $log = Log::createLog(
                            Auth::user()->id,
                            Log::TYPE_CARD_ASSIGNED_TO_USER,
                            'User ' . $employee['employee']['user']['full_name'] . ' has been assigned to task [' . $task->task_simple_name . ']',
                            $employee['id'],
                            $taskCard['id'],
                            'Xguard\LaravelKanban\Models\Task'
                        );
                        TaskVersion::create([
                            "index" => $taskCard['index'],
                            "name" => $taskCard['name'],
                            "deadline" => $taskCard['deadline'],
                            "shared_task_data_id" =>$taskCard['shared_task_data_id'],
                            "reporter_id" => $taskCard['reporter_id'],
                            "column_id" => $taskCard['column_id'],
                            "row_id" => $taskCard['row_id'],
                            "board_id" => $taskCard['board_id'],
                            "badge_id" => $taskCard['badge_id'],
                            "status" =>$taskCard['status'] ? $taskCard['status'] : 'active',
                            "task_id" => $taskCard['id'],
                            "log_id" => $log->id
                        ]);
                    }

                    foreach ($assignedToResponse['detached'] as $employeeId) {
                        $employee = Employee::with('user')->find($employeeId);
                        $log = Log::createLog(
                            Auth::user()->id,
                            Log::TYPE_CARD_UNASSIGNED_TO_USER,
                            'User ' . $employee['employee']['user']['full_name'] . ' has been removed from task [' . $task->task_simple_name . ']',
                            $employee['id'],
                            $taskCard['id'],
                            'Xguard\LaravelKanban\Models\Task'
                        );
                        TaskVersion::create([
                            "index" => $taskCard['index'],
                            "name" => $taskCard['name'],
                            "deadline" => $taskCard['deadline'],
                            "shared_task_data_id" =>$taskCard['shared_task_data_id'],
                            "reporter_id" => $taskCard['reporter_id'],
                            "column_id" => $taskCard['column_id'],
                            "row_id" => $taskCard['row_id'],
                            "board_id" => $taskCard['board_id'],
                            "badge_id" => $taskCard['badge_id'],
                            "status" =>$taskCard['status'] ? $taskCard['status'] : 'active',
                            "task_id" => $taskCard['id'],
                            "log_id" => $log->id
                        ]);
                    }

                    $badge = Badge::firstOrCreate([
                        'name' => count($request->input('badge')) > 0 ? $taskCard['badge']['name'] : '--'
                    ]);

                    if ($badge->wasRecentlyCreated) {
                        Log::createLog(
                            Auth::user()->id,
                            Log::TYPE_BADGE_CREATED,
                            "The badge [" . $badge->name . "] was created",
                            null,
                            $badge->id,
                            'Xguard\LaravelKanban\Models\Badge'
                        );
                    }
                }

                $sharedTaskDataId = $taskCard['shared_task_data_id'];

                if ($sharedTaskDataId === $task->shared_task_data_id) {
                    // update shared data if group hasn't changed

                    $sharedTaskData = SharedTaskData::where('id', $sharedTaskDataId)->first();
                    $sharedTaskData->update(['description' => $taskCard['shared_task_data']['description']]);

                    $erpEmployeesArray = [];
                    $erpContractsArray = [];

                    foreach ($taskCard['shared_task_data']['erp_employees'] as $erpEmployee) {
                        $erpEmployeesArray[$erpEmployee['id']] = ['shareable_type' => 'user'];
                    }


                    foreach ($taskCard['shared_task_data']['erp_contracts'] as $erpContract) {
                        $erpContractsArray[$erpContract['id']] = ['shareable_type' => 'contract'];
                    }

                    $sharedTaskData->erpContracts()->sync($erpContractsArray);
                    $sharedTaskData->erpEmployees()->sync($erpEmployeesArray);
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
                    $log = Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_CARD_UPDATED,
                        "[" . implode("','", array_keys($difference)) . "] was updated.",
                        null,
                        $task['id'],
                        'Xguard\LaravelKanban\Models\Task'
                    );
                    TaskVersion::create([
                        "index" => $task['index'],
                        "name" => $task['name'],
                        "deadline" => $task['deadline'],
                        "shared_task_data_id" =>$task['shared_task_data_id'],
                        "reporter_id" => $task['reporter_id'],
                        "column_id" => $task['column_id'],
                        "row_id" => $task['row_id'],
                        "board_id" => $task['board_id'],
                        "badge_id" => $task['badge_id'],
                        "status" => $task['status'] ? $task['status'] : 'active',
                        "task_id" => $task['id'],
                        "log_id" => $log->id
                    ]);  
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
            ->with(['reporter.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['sharedTaskData' => function ($q) {
                $q->with(['erpContracts' => function ($q) {
                    $q->select(['contracts.id', 'contract_identifier']);
                }])->with(['erpEmployees' => function ($q) {
                    $q->select(['users.id', 'first_name', 'last_name']);
                }]);
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

            $log = Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_MOVED,
                'Task [' . $task->task_simple_name . '] changed from [' . $prevRow . ':' . $prevColumn . '] to [' . $row . ':' . $column . ']',
                null,
                $task->id,
                'Xguard\LaravelKanban\Models\Task'
            );
            TaskVersion::create([
                "index" => $task->index,
                "name" => $task->name,
                "deadline" => $task->deadline,
                "shared_task_data_id" =>$task->shared_task_data_id,
                "reporter_id" => $task->reporter_id,
                "column_id" => $task->column_id,
                "row_id" => $task->row_id,
                "board_id" => $task->board_id,
                "badge_id" => $task->badge_id,
                "status" => $task->status ? $task->status : 'active',
                "task_id" => $task->id,
                "log_id" => $log->id
            ]); 
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
                    $log = Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_CARD_CHECKLIST_ITEM_CHECKED,
                        'Checked -> ' . $descriptionData['checkboxContent'],
                        null,
                        $taskCard->id,
                        'Xguard\LaravelKanban\Models\Task'
                    );
                    TaskVersion::create([
                        "index" => $taskCard->index,
                        "name" => $taskCard->name,
                        "deadline" => $taskCard->deadline,
                        "shared_task_data_id" =>$taskCard->shared_task_data_id,
                        "reporter_id" => $taskCard->reporter_id,
                        "column_id" => $taskCard->column_id,
                        "row_id" => $taskCard->row_id,
                        "board_id" => $taskCard->board_id,
                        "badge_id" => $taskCard->badge_id,
                        "status" => $taskCard->status ? $taskCard->status : 'active',
                        "task_id" => $taskCard->id,
                        "log_id" => $log->id
                    ]);
                } else {
                    $log = Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_CARD_CHECKLIST_ITEM_UNCHECKED,
                        'Unchecked -> ' . $descriptionData['checkboxContent'],
                        null,
                        $taskCard->id,
                        'Xguard\LaravelKanban\Models\Task'
                    );
                    TaskVersion::create([
                        "index" => $taskCard->index,
                        "name" => $taskCard->name,
                        "deadline" => $taskCard->deadline,
                        "shared_task_data_id" =>$taskCard->shared_task_data_id,
                        "reporter_id" => $taskCard->reporter_id,
                        "column_id" => $taskCard->column_id,
                        "row_id" => $taskCard->row_id,
                        "board_id" => $taskCard->board_id,
                        "badge_id" => $taskCard->badge_id,
                        "status" => $taskCard->status ? $taskCard->status : 'active',
                        "task_id" => $taskCard->id,
                        "log_id" => $log->id
                    ]);
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
                $log = Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_COMPLETED,
                    'Task [' . $task->task_simple_name . '] in board [' . $task->board->name . '] set to completed',
                    null,
                    $task->id,
                    'Xguard\LaravelKanban\Models\Task'
                );
                TaskVersion::create([
                    "index" => $task->index,
                    "name" => $task->name,
                    "deadline" => $task->deadline,
                    "shared_task_data_id" =>$task->shared_task_data_id,
                    "reporter_id" => $task->reporter_id,
                    "column_id" => $task->column_id,
                    "row_id" => $task->row_id,
                    "board_id" => $task->board_id,
                    "badge_id" => $task->badge_id,
                    "status" => $task->status ? $task->status : 'active',
                    "task_id" => $task->id,
                    "log_id" => $log->id
                ]); 
            } elseif ($status === 'cancelled') {
                $log = Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_CANCELED,
                    'Task [' . $task->task_simple_name . '] in board [' . $task->board->name . '] set to cancelled',
                    null,
                    $task->id,
                    'Xguard\LaravelKanban\Models\Task'
                );
                TaskVersion::create([
                    "index" => $task->index,
                    "name" => $task->name,
                    "deadline" => $task->deadline,
                    "shared_task_data_id" =>$task->shared_task_data_id,
                    "reporter_id" => $task->reporter_id,
                    "column_id" => $task->column_id,
                    "row_id" => $task->row_id,
                    "board_id" => $task->board_id,
                    "badge_id" => $task->badge_id,
                    "status" => $task->status ? $task->status : 'active',
                    "task_id" => $task->id,
                    "log_id" => $log->id
                ]); 
            } elseif ($status === 'active') {
                $log = Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_CARD_CANCELED,
                    'Task [' . $task->task_simple_name . '] in board [' . $task->board->name . '] set to active',
                    null,
                    $task->id,
                    'Xguard\LaravelKanban\Models\Task'
                );
                TaskVersion::create([
                    "index" => $task->index,
                    "name" => $task->name,
                    "deadline" => $task->deadline,
                    "shared_task_data_id" =>$task->shared_task_data_id,
                    "reporter_id" => $task->reporter_id,
                    "column_id" => $task->column_id,
                    "row_id" => $task->row_id,
                    "board_id" => $task->board_id,
                    "badge_id" => $task->badge_id,
                    "status" => $task->status ? $task->status : 'active',
                    "task_id" => $task->id,
                    "log_id" => $log->id
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

    public function assignTaskToBoard($task_id, $row_id, $column_id, $board_id)
    {
        try {
            $task = Task::with('board')->get()->find($task_id);
            $task->update([
                'board_id' => $board_id,
                'row_id' => $row_id,
                'column_id' => $column_id
            ]);

            $log = Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_ASSIGNED_TO_BOARD,
                'Task [' . $task->task_simple_name . '] assigned to board [' . $task->board->name . '] on [' . $task->row->name . ':' . $task->column->name . ']',
                null,
                $task->id,
                'Xguard\LaravelKanban\Models\Task'
            );
            TaskVersion::create([
                "index" => $task->index,
                "name" => $task->name,
                "deadline" => $task->deadline,
                "shared_task_data_id" =>$task->shared_task_data_id,
                "reporter_id" => $task->reporter_id,
                "column_id" => $task->column_id,
                "row_id" => $task->row_id,
                "board_id" => $task->board_id,
                "badge_id" => $task->badge_id,
                "status" => $task->status ? $task->status : 'active',
                "task_id" => $task->id,
                "log_id" => $log->id
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
            $prevGroup = $taskCard->shared_task_data_id;


            $taskCard->update([
                'shared_task_data_id' => $group,
            ]);

            // delete previous group if no other tasks point to it
            $tasksWithSharedTaskData = Task::where('shared_task_data_id', $prevGroup)->count();
            if ($tasksWithSharedTaskData === 0) {
                SharedTaskData::where('id', $prevGroup)->delete();
            }

            $log = Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_ASSIGNED_GROUP,
                'Task [' . $taskCard->task_simple_name . '] changed group',
                null,
                $taskCard->id,
                'Xguard\LaravelKanban\Models\Task'
            );
            TaskVersion::create([
                "index" => $taskCard->index,
                "name" => $taskCard->name,
                "deadline" => $taskCard->deadline,
                "shared_task_data_id" =>$taskCard->shared_task_data_id,
                "reporter_id" => $taskCard->reporter_id,
                "column_id" => $taskCard->column_id,
                "row_id" => $taskCard->row_id,
                "board_id" => $taskCard->board_id,
                "badge_id" => $taskCard->badge_id,
                "status" => $taskCard->status ? $taskCard->status : 'active',
                "task_id" => $taskCard->id,
                "log_id" => $log->id
            ]); 
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

            $log = Log::createLog(
                Auth::user()->id,
                Log::TYPE_CARD_ASSIGNED_GROUP,
                'Task [' . $taskCard->task_simple_name . '] was removed from group',
                null,
                $taskCard->id,
                'Xguard\LaravelKanban\Models\Task'
            );

            TaskVersion::create([
                "index" => $taskCard->index,
                "name" => $taskCard->name,
                "deadline" => $taskCard->deadline,
                "shared_task_data_id" =>$taskCard->shared_task_data_id,
                "reporter_id" => $taskCard->reporter_id,
                "column_id" => $taskCard->column_id,
                "row_id" => $taskCard->row_id,
                "board_id" => $taskCard->board_id,
                "badge_id" => $taskCard->badge_id,
                "status" => $taskCard->status ? $taskCard->status : 'active',
                "task_id" => $taskCard->id,
                "log_id" => $log->id
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
