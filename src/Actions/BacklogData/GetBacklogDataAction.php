<?php

namespace Xguard\LaravelKanban\Actions\BacklogData;

use DateTime;
use Exception;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Actions\Badges\ListBadgesWithCountAction;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\Template;

class GetBacklogDataAction extends Action
{

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'start' => ['required', 'date','before_or_equal:today'],
            'end' => ['required','date','after_or_equal:start']
        ];
    }

    public function messages(): array
    {
        return [
            'start.required' => 'Start date is required',
            'start.date' => 'Start date needs to be in a date format',
            'start.before_or_equal' => 'Start date cannot be in the future',
            'end.required' => 'End date is required',
            'end.date' => 'End date needs to be in a date format',
            'end.after' => 'End date needs to be after start date'
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function handle(): array
    {

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
            $boardArray[$board->id] = (object) [
                'id' => $board->id,
                'name' => $board->name,
                'assigned' => 0,
                'total' => 0,
                'active' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'placed_in_board' => 0,
            ];
        }

        $backlogTasks = Task::whereDate('created_at', '>=', new DateTime($this->start))
            ->whereDate('created_at', '<=', new DateTime($this->end))
            ->whereHas('board', function ($q) use ($boardArray) {
                $q->whereIn('id', array_keys($boardArray));
            })->get();


        foreach ($backlogTasks as $task) {
            $boardArray[$task->board_id]->total += 1;

            if ($task->status === "active") {
                $boardArray[$task->board_id]->active += 1;
                if (count($task->assignedTo) > 0) {
                    $boardArray[$task->board_id]->assigned += 1;
                }
            }
            if ($task->status === "completed") {
                $boardArray[$task->board_id]->completed += 1;
            }
            if ($task->status === "cancelled") {
                $boardArray[$task->board_id]->cancelled += 1;
            }
            if ($task->row_id !== null) {
                $boardArray[$task->board_id]->placed_in_board += 1;
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
}
