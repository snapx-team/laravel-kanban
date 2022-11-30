<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use App\Actions\Traits\Pagination;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Enums\Roles;
use Xguard\LaravelKanban\Enums\SessionVariables;
use Xguard\LaravelKanban\Enums\TaskStatuses;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Models\Task;
use DateTime;

class PaginateBackLogTasksAction extends Action
{
    use Pagination;

    const ERP_CONTRACTS = 'erpContracts';
    const CONTRACTS_ID = 'contracts.id';
    const FILTER_START = 'filterStart';
    const FILTER_END = 'filterEnd';
    const FILTER_STATUS = 'filterStatus';
    const FILTER_PLACED_IN_BOARD = 'filterPlacedInBoard';
    const FILTER_NOT_PLACED_IN_BOARD = 'filterNotPlacedInBoard';
    const FILTER_TEXT = 'filterText';
    const FILTER_BADGE = 'filterBadge';
    const FILTER_BOARD = 'filterBoard';
    const FILTER_REPORTER = 'filterReporter';
    const FILTER_ASSIGNED_TO = 'filterAssignedTo';
    const FILTER_ERP_EMPLOYEE = 'filterErpEmployee';
    const FILTER_ERP_CONTRACT = 'filterErpContract';
    const ID = 'id';
    const GTE = '>=';
    const LTE = '<=';
    const EMPLOYEE_ID = 'employee_id';
    const USERS_ID = 'users.id';

    public function rules(): array
    {
        return [
            self::FILTER_START => ['required', 'string'],
            self::FILTER_END => ['required', 'string'],
            self::FILTER_STATUS => ['present', 'array'],
            self::FILTER_PLACED_IN_BOARD => ['required', 'boolean'],
            self::FILTER_NOT_PLACED_IN_BOARD => ['required', 'boolean'],
            self::FILTER_TEXT => ['nullable', 'string'],
            self::FILTER_BADGE => ['present', 'array'],
            self::FILTER_BOARD => ['present', 'array'],
            self::FILTER_ASSIGNED_TO => ['present', 'array'],
            self::FILTER_REPORTER => ['present', 'array'],
            self::FILTER_ERP_EMPLOYEE => ['present', 'array'],
            self::FILTER_ERP_CONTRACT => ['present', 'array'],
        ];
    }

    public function handle()
    {
        $query = Task::with(Task::BADGE_RELATION_NAME, Task::ROW_RELATION_NAME, Task::COLUMN_RELATION_NAME, Task::BOARD_RELATION_NAME, Task::SHARED_TASK_DATA_RELATION_NAME)
            ->withTaskData()
            ->orderBy(Task::DEADLINE)
            ->whereDate(Task::CREATED_AT, self::GTE, new DateTime($this->filterStart))
            ->whereDate(Task::CREATED_AT, self::LTE, new DateTime($this->filterEnd))
            ->whereIn(Task::STATUS, $this->filterStatus);

        if (session(SessionVariables::ROLE()->getValue()) === Roles::EMPLOYEE()->getValue()) {
            $query->whereHas(Task::BOARD_RELATION_NAME .'.'. Board::MEMBERS_RELATION_NAME, function ($q) {
                $q->where(Member::EMPLOYEE_ID, session(SessionVariables::EMPLOYEE_ID()->getValue()));
            });
        }

        if (!in_array(TaskStatuses::CANCELLED()->getValue(), $this->filterStatus) || !in_array(TaskStatuses::COMPLETED()->getValue(), $this->filterStatus)) {
            if (!$this->filterPlacedInBoard) {
                $query->whereNull(Task::COLUMN_ID);
            }
            if (!$this->filterNotPlacedInBoard) {
                $query->whereNotNull(Task::COLUMN_ID);
            }
        }

        if ($this->filterText) {
            $query = $query->whereSearchText($this->filterText);
        }

        if (!empty($this->filterBadge)) {
            $badgeIds = array_column($this->filterBadge, self::ID);
            $query->whereHas(Task::BADGE_RELATION_NAME, function ($q) use ($badgeIds) {
                $q->whereIn(Badge::ID, $badgeIds);
            });
        }

        if (!empty($this->filterBoard)) {
            $boardIds = array_column($this->filterBoard, self::ID);
            $query->whereHas(Task::BOARD_RELATION_NAME, function ($q) use ($boardIds) {
                $q->whereIn(Board::ID, $boardIds);
            });
        }

        if (!empty($this->filterAssignedTo)) {
            $assignedToIds = array_column($this->filterAssignedTo, self::ID);
            $query->whereHas(Task::ASSIGNED_TO_RELATION_NAME, function ($q) use ($assignedToIds) {
                $q->whereIn(self::EMPLOYEE_ID, $assignedToIds);
            });
        }

        if (!empty($this->filterReporter)) {
            $reporterIds = array_column($this->filterReporter, self::ID);
            $query->whereIn(Task::REPORTER_ID, $reporterIds);
        }

        if (!empty($this->filterErpEmployee)) {
            $erpEmployeeIds = array_column($this->filterErpEmployee, self::ID);
            $query->whereHas(Task::SHARED_TASK_DATA_RELATION_NAME, function ($q) use ($erpEmployeeIds) {
                $q->whereHas(SharedTaskData::ERP_EMPLOYEES_RELATION_NAME, function ($q) use ($erpEmployeeIds) {
                    $q->whereIn(self::USERS_ID, $erpEmployeeIds);
                });
            });
        }

        if (!empty($this->filterErpContract)) {
            $erpContractsIds = array_column($this->filterErpContract, self::ID);
            $query->whereHas(Task::SHARED_TASK_DATA_RELATION_NAME, function ($q) use ($erpContractsIds) {
                $q->whereHas(self::ERP_CONTRACTS, function ($q) use ($erpContractsIds) {
                    $q->whereIn(self::CONTRACTS_ID, $erpContractsIds);
                });
            });
        }

        return $query->paginate(15);
    }
}
