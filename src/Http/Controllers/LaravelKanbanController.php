<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use DateTime;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Board;
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
        $board = Board::with('rows.columns.taskCards.badge')
            ->with(['rows.columns.taskCards.assignedTo.user' => function($q){
                $q->select(['id','first_name','last_name']);
            }])
            ->with(['rows.columns.taskCards.erpEmployee' => function($q){
                $q->select(['id','first_name','last_name']);
            }])
            ->with(['rows.columns.taskCards.reporter' => function($q){
                $q->select(['id','first_name','last_name']);
            }])
            ->with(['members.employee.user' => function($q){
                $q->select(['id','first_name','last_name']);
            }])
            ->with(['rows.columns.taskCards.erpJobSite' => function($q){
                $q->select(['id','name']);
            }])
            ->with(['rows.columns.taskCards.row', 'rows.columns.taskCards.column',])
            ->find($id);
        return $board;
    }

    public function getDashboardData()
    {
        $employees = Employee::with('user')->get();

        $board = Board::orderBy('name')->with('members')->get();
        return [
            'employees' => $employees,
            'boards' => $board
        ];
    }

    public function getBacklogData() {
        $backlogTasks = Task::with('badge', 'board', 'reporter', 'assignedTo.user')->orderBy('deadline')->get();
        $boards = Board::orderBy('name')->with('members')->get();

        $boardArray = [];
        foreach ($boards as $board) {
            $boardArray[$board->id] = (object) [
                'title' => $board->name, 
                'percent' => 0,
                'total' => 0,
                'active' => 0,
                'completed' => 0,
                'canceled' => 0,
                'unassigned' => 0,
                'total' => 0,
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
            if($task->status === "active") {
                $boardArray[$task->board_id]->active += 1;
            }
            if($task->status === "completed") {
                $boardArray[$task->board_id]->completed += 1;
            }
            if($task->status === "canceled") {
                $boardArray[$task->board_id]->canceled += 1;
            }
        }

        return [
            'boards' => $boardArray,
            'backlogTasks' => $backlogTasks,
            'badges' => $badgeArray
        ];
    }

    public function getFormattedData($id)
    {
        $kanbanData = Board::with('members.employee.user', 'rows.columns.taskCards.employee.user')->find($id);

        $rows = [];

        foreach ($kanbanData['rows'] as $row) {
            $columns = [];
            foreach ($row['columns'] as $column) {
                $taskCards = [];
                $employeeIndex = 1;
                foreach ($column['taskCards'] as $taskCard) {
                    if ((int)$taskCard['employee']['is_active']) {
                        $taskCardData = ['index' => $employeeIndex, 'phone' => $taskCard['employee']['phone'], 'name' => $taskCard['employee']['name'],];
                        array_push($taskCards, $taskCardData);
                        $employeeIndex++;
                    }
                }
                $columnData = ['timespan' => $column['name'], 'start' => $column['shift_start'], 'end' => $column['shift_end'], 'employee' => $taskCards,];
                array_push($columns, $columnData);
            }
            $rowData = ['day' => $row['name'], 'shifts' => $columns,];
            array_push($rows, $rowData);
        }
        return json_encode($rows);
    }

    public function getAvailableAgent($id, $level)
    {
        $level--; // we start at index 0
        $kanbanData = Board::with('members.employee', 'rows.columns.taskCards.employee')->find($id);

        date_default_timezone_set('America/Montreal');

        $dayOfWeek = date("l");
        $currentTime = date('h:i a');

        foreach ($kanbanData['rows'] as $row) {

            if ($row['name'] === $dayOfWeek) {

                foreach ($row['columns'] as $column) {
                    $now = DateTime::createFromFormat('h:i a', $currentTime);
                    $start = DateTime::createFromFormat('h:i a', $column['shift_start']);
                    $end = DateTime::createFromFormat('h:i a', $column['shift_end']);

                    if ($start > $end) $end->modify('+1 day');
                    if ($start <= $now && $now <= $end || $start <= $now->modify('+1 day') && $now <= $end) {
                        try {

                            $filtered = $column->taskCards->filter(function ($item) {
                                return data_get($item->employee, 'is_active') === 1;
                            });

                            $phone = $filtered->values()->get($level)->employee->phone;
                            $name = $filtered->values()->get($level)->employee->name;
                        } catch (\Exception $e) {
                            return [];
                        }
                        return ['name' => $name, 'phone' => $phone,];
                    }
                }
            }
        }
        return [];
    }
}
