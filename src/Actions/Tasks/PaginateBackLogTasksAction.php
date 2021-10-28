<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use App\Actions\Traits\Pagination;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Task;
use DateTime;

class PaginateBackLogTasksAction extends Action
{
    use Pagination;
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'filterStart' => ['required', 'string'],
            'filterEnd' => ['required', 'string'],
            'filterStatus' => ['present', 'array'],
            'filterPlacedInBoard' => ['required', 'boolean'],
            'filterNotPlacedInBoard' => ['required', 'boolean'],
            'filterText' => ['nullable', 'string'],
            'filterBadge' => ['present', 'array'],
            'filterBoard' => ['present', 'array'],
            'filterAssignedTo' => ['present', 'array'],
            'filterReporter' => ['present', 'array'],
            'filterErpEmployee' => ['present', 'array'],
            'filterErpContract' => ['present', 'array'],
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $query = Task::with('badge', 'row', 'column', 'board', 'sharedTaskData')
            ->withTaskData()
            ->orderBy('deadline')
            ->whereDate('created_at', '>=', new DateTime($this->filterStart))
            ->whereDate('created_at', '<=', new DateTime($this->filterEnd))
            ->whereIn('status', $this->filterStatus);

        if (session('role') === 'employee') {
            $query->whereHas('board.members', function ($q) {
                $q->where('employee_id', session('employee_id'));
            });
        }

        if (!$this->filterPlacedInBoard) {
            $query->whereNull('column_id');
        }
        if (!$this->filterNotPlacedInBoard) {
            $query->whereNotNull('column_id');
        }

        if ($this->filterText) {
            $query = $query->whereSearchText($this->filterText);
        }

        if (!empty($this->filterBadge)) {
            $badgeIds = array_column($this->filterBadge, 'id');
            $query->whereHas('badge', function ($q) use ($badgeIds) {
                $q->whereIn('id', $badgeIds);
            });
        }

        if (!empty($this->filterBoard)) {
            $boardIds = array_column($this->filterBoard, 'id');
            $query->whereHas('board', function ($q) use ($boardIds) {
                $q->whereIn('id', $boardIds);
            });
        }


        if (!empty($this->filterAssignedTo)) {
            $assignedToIds = array_column($this->filterAssignedTo, 'id');
            $query->whereHas('assignedTo', function ($q) use ($assignedToIds) {
                $q->whereIn('employee_id', $assignedToIds);
            });
        }

        if (!empty($this->filterReporter)) {
            $reporterIds = array_column($this->filterReporter, 'id');
            $query->whereIn('reporter_id', $reporterIds);
        }

        if (!empty($this->filterErpEmployee)) {
            $erpEmployeeIds = array_column($this->filterErpEmployee, 'id');
            $query->whereHas('sharedTaskData', function ($q) use ($erpEmployeeIds) {
                $q->whereHas('erpEmployees', function ($q) use ($erpEmployeeIds) {
                    $q->whereIn('users.id', $erpEmployeeIds);
                });
            });
        }

        if (!empty($this->filterErpContract)) {
            $erpContractsIds = array_column($this->filterErpContract, 'id');
            $query->whereHas('sharedTaskData', function ($q) use ($erpContractsIds) {
                $q->whereHas('erpContracts', function ($q) use ($erpContractsIds) {
                    $q->whereIn('contracts.id', $erpContractsIds);
                });
            });
        }

        return $query->paginate(15);
    }
}
