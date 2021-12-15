<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use DateTime;
use Exception;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

class GetTaskHashAction extends Action
{

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'taskId' => ['required', 'numeric'],
        ];
    }

    /**
     * @return string
     * @throws Exception
     */
    public function handle(): string
    {
        $data = Task::select([
            'name', 'deadline', 'shared_task_data_id', 'reporter_id', 'column_id', 'row_id', 'board_id',
            'badge_id', 'status', 'time_estimate'
        ])
            ->with('badge', 'board', 'row', 'column', 'taskFiles', 'sharedTaskData')
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
            ])->find($this->taskId)->toJson();

        return sha1($data);
    }
}
