<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use DateTime;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Template;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\Badge;

class LaravelKanbanController extends Controller
{
    public function getIndex()
    {

        return view('Xguard\LaravelKanban::index');
    }

    public function getkanbanData($id)
    {
        return Board::with('rows.columns.taskCards.badge')
            ->with(['rows.columns.taskCards.assignedTo.employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['rows.columns.taskCards.erpEmployee' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['rows.columns.taskCards.reporter' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['members.employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['rows.columns.taskCards.erpJobSite' => function ($q) {
                $q->select(['id', 'name']);
            }])
            ->with(['rows.columns.taskCards.row', 'rows.columns.taskCards.column',])
            ->find($id);
    }

    public function getDashboardData()
    {
        $employees = Employee::with('user')->get();
        $boards = Board::orderBy('name')->with('members')->get();
        $templates = Template::orderBy('name')->with('badge')->get();

        return [
            'employees' => $employees,
            'boards' => $boards,
            'templates' => $templates
        ];
    }

    public function getBacklogData()
    {
        $backlogTasks = Task::
        with('badge', 'board', 'reporter', 'assignedTo.employee.user', 'erpJobSite', 'erpEmployee')
            ->orderBy('deadline')
            ->get();
        $boards = Board::orderBy('name')->with('members')->get();
        $kanbanUsers = Employee::with('user')->get();

        $boardArray = [];
        foreach ($boards as $board) {
            $boardArray[$board->id] = (object)[
                'name' => $board->name,
                'percent' => 0,
                'total' => 0,
                'active' => 0,
                'completed' => 0,
                'canceled' => 0,
                'unassigned' => 0,
            ];
        }

        $badgeArray = [];
        foreach ($backlogTasks as $task) {
            if (count($task->assignedTo) > 0) {
                $boardArray[$task->board_id]->percent += 1;
            } else {
                $boardArray[$task->board_id]->unassigned += 1;
            }
            if (array_key_exists($task->board_id, $boardArray)) {
                $boardArray[$task->board_id]->total += 1;
            }
            if (!in_array($task->badge->name, $badgeArray)) {
                array_push($badgeArray, $task->badge->name);
            }
            if ($task->status === "active") {
                $boardArray[$task->board_id]->active += 1;
            }
            if ($task->status === "completed") {
                $boardArray[$task->board_id]->completed += 1;
            }
            if ($task->status === "canceled") {
                $boardArray[$task->board_id]->canceled += 1;
            }
        }

        return [
            'boards' => $boardArray,
            'allBoards' => $boards,
            'backlogTasks' => $backlogTasks,
            'badges' => $badgeArray,
            'kanbanUsers' => $kanbanUsers,
        ];
    }

}
