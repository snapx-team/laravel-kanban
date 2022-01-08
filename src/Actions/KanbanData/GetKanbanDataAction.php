<?php

namespace Xguard\LaravelKanban\Actions\KanbanData;

use Exception;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Enums\TaskStatuses;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Column;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Row;
use Xguard\LaravelKanban\Models\SharedTaskData;
use Xguard\LaravelKanban\Models\Task;

class GetKanbanDataAction extends Action
{
    public function authorize(): bool
    {
        return AccessManager::canAccessBoard($this->boardId);
    }

    protected function failedAuthorization()
    {
        abort(403, "You don't have access to this board");
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'boardId' => ['required', 'integer'],
        ];
    }

    /**
     * @return object
     * @throws Exception
     */
    public function handle(): ?object
    {
        return Board::with([
            Board::ROWS_RELATION_NAME.'.'.Row::COLUMNS_RELATION_NAME.'.'.Column::TASK_CARDS_RELATION_NAME=> function ($q) {
                $q->where(Task::STATUS, TaskStatuses::ACTIVE()->getValue())
                    ->with(Task::BADGE_RELATION_NAME, Task::ROW_RELATION_NAME, Task::COLUMN_RELATION_NAME, Task::TASK_FILES_RELATION_NAME)
                    ->with([
                        Task::SHARED_TASK_DATA_RELATION_NAME => function ($q) {
                            $q->with([
                                SharedTaskData::ERP_CONTRACTS_RELATION_NAME => function ($q) {
                                    $q->select(['contracts.id', 'contract_identifier']);
                                }
                            ])->with([
                                SharedTaskData::ERP_EMPLOYEES_RELATION_NAME => function ($q) {
                                    $q->select(['users.id', 'first_name', 'last_name']);
                                }
                            ]);
                        }
                    ])
                    ->with([
                        Task::ASSIGNED_TO_RELATION_NAME.'.employee.user' => function ($q) {
                            $q->select(['id', 'first_name', 'last_name']);
                        }
                    ])
                    ->with([
                        Task::REPORTER_RELATION_NAME.'.user' => function ($q) {
                            $q->select(['id', 'first_name', 'last_name']);
                        }
                    ]);
            }
        ])->with([
            Board::MEMBERS_RELATION_NAME.'.'.Member::EMPLOYEE_RELATION_NAME.'.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }
        ])->find($this->boardId);
    }
}
