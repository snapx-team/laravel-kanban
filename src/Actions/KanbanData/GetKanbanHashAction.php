<?php

namespace Xguard\LaravelKanban\Actions\KanbanData;

use DateTime;
use Exception;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Log;

class GetKanbanHashAction extends Action
{

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
     * @return string
     * @throws Exception
     */
    public function handle(): string
    {
        $data = Board::with([
            'rows.columns.taskCards' => function ($q) {
                $q->where('status', 'active')
                    ->select([
                        'name', 'deadline', 'shared_task_data_id', 'reporter_id', 'column_id', 'row_id', 'board_id',
                        'badge_id', 'status', 'time_estimate'
                    ])
                    ->with('badge', 'board', 'row', 'column', 'taskFiles')
                    ->with([
                        'sharedTaskData' => function ($q) {
                            $q->with([
                                'erpContracts' => function ($q) {
                                    $q->select(['contracts.id', 'contract_identifier']);
                                }
                            ])->with([
                                'erpEmployees' => function ($q) {
                                    $q->select(['users.id', 'first_name', 'last_name']);
                                }
                            ]);
                        }
                    ])
                    ->with([
                        'assignedTo.employee.user' => function ($q) {
                            $q->select(['id', 'first_name', 'last_name']);
                        }
                    ])
                    ->with([
                        'reporter.user' => function ($q) {
                            $q->select(['id', 'first_name', 'last_name']);
                        }
                    ]);
            }
        ])->with([
            'members.employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }
        ])->find($this->boardId)->toJson();

        return sha1($data);
    }
}
