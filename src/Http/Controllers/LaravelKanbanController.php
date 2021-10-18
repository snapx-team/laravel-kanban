<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\JsonResponse;
use DateTime;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Actions\Badge\ListBadgesWithCountAction;
use Xguard\LaravelKanban\Http\Helper\CheckHasAccessToBoardWithBoardId;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Comment;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Template;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Actions\Users\GetUserProfileAction;

class LaravelKanbanController extends Controller
{
    public function getIndex()
    {
        $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
        session(['role' => $employee->role, 'employee_id' => $employee->id]);
        return view('Xguard\LaravelKanban::index');
    }

    public function getRoleAndEmployeeId(): array
    {
        return [
            'role' => session('role'),
            'employee_id' => session('employee_id'),
        ];
    }

    public function getFooterInfo(): array
    {
        return [
            'parent_name' => config('laravel_kanban.parent_name'),
            'version' => config('laravel_kanban.version'),
            'date' => date("Y")
        ];
    }

    public function getkanbanData($id)
    {
        $hasBoardAccess = (new CheckHasAccessToBoardWithBoardId())->returnBool($id);

        if ($hasBoardAccess) {
            return Board::with(['rows.columns.taskCards' => function ($q) {
                $q->where('status', 'active')
                    ->with('badge', 'board', 'row', 'column')
                    ->with(['sharedTaskData' => function ($q) {
                        $q->with(['erpContracts' => function ($q) {
                            $q->select(['contracts.id', 'contract_identifier']);
                        }])->with(['erpEmployees' => function ($q) {
                            $q->select(['users.id', 'first_name', 'last_name']);
                        }]);
                    }])
                    ->with(['assignedTo.employee.user' => function ($q) {
                        $q->select(['id', 'first_name', 'last_name']);
                    }])
                    ->with(['reporter.user' => function ($q) {
                        $q->select(['id', 'first_name', 'last_name']);
                    }]);
            }])->with(['members.employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])->find($id);
        } else {
            abort(403, "You don't have access to this board");
        }
    }

    public function getDashboardData()
    {
        if (session('role') === 'admin') {
            $boards = Board::orderBy('name')->with('members')->get();
        } else {
            $boards = Board::orderBy('name')->
            whereHas('members', function ($q) {
                $q->where('employee_id', session('employee_id'));
            })->with('members')->get();
        }
        $employees = Employee::with('user')->get();
        $templates = Template::orderBy('name')->with('badge', 'boards')->get();
        $badges = app(ListBadgesWithCountAction::class)->run();

        return [
            'employees' => $employees,
            'boards' => $boards,
            'templates' => $templates,
            'badges' => $badges
        ];
    }

    public function getBacklogData($start, $end)
    {

        if (session('role') === 'admin') {
            $backlogTasks = Task::
            with('badge', 'row', 'column', 'board', 'sharedTaskData')
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
                ->whereDate('created_at', '>=', new DateTime($start))
                ->whereDate('created_at', '<=', new DateTime($end))
                ->orderBy('deadline')
                ->get();
            $boards = Board::orderBy('name')->with('members')->get();
        } else {
            $backlogTasks = Task::with('board', 'badge', 'row', 'column')
                ->whereHas('board.members', function ($q) {
                    $q->where('employee_id', session('employee_id'));
                })
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
                ->whereDate('created_at', '>=', new DateTime($start))
                ->whereDate('created_at', '<=', new DateTime($end))
                ->orderBy('deadline')
                ->get();

            $boards = Board::orderBy('name')->
            whereHas('members', function ($q) {
                $q->where('employee_id', session('employee_id'));
            })->with('members')->get();
        }

        $kanbanUsers = Employee::with('user')->get();

        $boardArray = [];
        foreach ($boards as $key => $board) {
            $boardArray[$board->id] = (object)[
                'id' => $board->id,
                'name' => $board->name,
                'percent' => 0,
                'total' => 0,
                'active' => 0,
                'completed' => 0,
                'canceled' => 0,
                'unassigned' => 0,
            ];
        }

        foreach ($backlogTasks as $task) {
            if ($task->status === "active" && $task->row_id !== null) {
                $boardArray[$task->board_id]->active += 1;
                if (count($task->assignedTo) > 0) {
                    $boardArray[$task->board_id]->percent += 1;
                } else {
                    $boardArray[$task->board_id]->unassigned += 1;
                }
                if (array_key_exists($task->board_id, $boardArray)) {
                    $boardArray[$task->board_id]->total += 1;
                }
            }
            if ($task->status === "completed") {
                $boardArray[$task->board_id]->completed += 1;
            }
            if ($task->status === "cancelled") {
                $boardArray[$task->board_id]->canceled += 1;
            }
        }

        $badgeArray = Badge::all();

        return [
            'boards' => $boardArray,
            'allBoards' => $boards,
            'backlogTasks' => [],
            'badges' => $badgeArray,
            'kanbanUsers' => $kanbanUsers,
        ];
    }

    public function getNotificationData($logType)
    {
        if ($logType !== "null") {
            $type = (int)$logType;
            $employee = Employee::
            where('user_id', '=', Auth::user()->id)
                ->with('notifications.user')
                ->with(['notifications' => function ($q) use ($type) {
                    $q->where('log_type', $type)
                        ->orderBy('created_at', 'desc')
                        ->with(['loggable' => function (MorphTo $morphTo) {
                            $morphTo->morphWith([Task::class, Comment::class => ['task.board'], Board::class]);
                        }])->paginate(10);
                }])->first();
        } else {
            $employee = Employee::
            where('user_id', '=', Auth::user()->id)
                ->with('notifications.user')
                ->with(['notifications' => function ($q) {
                    $q->orderBy('created_at', 'desc')
                        ->with(['loggable' => function (MorphTo $morphTo) {
                            $morphTo->morphWith([Task::class, Comment::class => ['task.board'], Board::class]);
                        }])
                        ->paginate(10);
                }])->first();
        }
        return $employee->notifications;
    }

    public function getNotificationCount()
    {
        $employee = Employee::
        where('user_id', '=', Auth::user()->id)
            ->first();
        $startDate = $employee->last_notif_check;
        if ($startDate !== null) {
            $employee = Employee::
            where('user_id', '=', Auth::user()->id)
                ->with(['notifications' => function ($q) use ($startDate) {
                    $q->where('kanban_logs.created_at', '>=', new DateTime($startDate));
                }])
                ->first();
        }
        if ($employee == null) {
            return 0;
        }
        return count($employee->notifications);
    }

    public function updateNotificationCount()
    {
        try {
            Employee::where('user_id', '=', Auth::user()->id)->update([
                'last_notif_check' => new DateTime('NOW')
            ]);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function getUserProfile()
    {
        try {
            $profile = (new GetUserProfileAction)->run();
            return new JsonResponse($profile);
        } catch (\Exception $e) {
            return new JsonResponse(null, 404, 'The user profile couldn\'t be retrieved');
        }
    }
}
