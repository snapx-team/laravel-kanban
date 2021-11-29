<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\JsonResponse;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Actions\Badges\ListBadgesWithCountAction;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Board;
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
        $hasBoardAccess = AccessManager::canAccessBoard($id);

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

    public function getBacklogData($start, $end): array
    {

        $backlogTasks = Task::with('badge', 'row', 'column', 'board');

        if (session('role') === 'admin') {
            $backlogTasks->whereHas('board.members', function ($q) {
                $q->where('employee_id', session('employee_id'));
            });
        }
        $backlogTasks->with(['reporter.user' => function ($q) {
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

        if (session('role') === 'admin') {
            $boards = Board::orderBy('name')->with('members')->get();
        } else {
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
                'cancelled' => 0,
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
                $boardArray[$task->board_id]->cancelled += 1;
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
