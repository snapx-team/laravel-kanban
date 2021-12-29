<?php

namespace Xguard\LaravelKanban\Actions\KanbanData;

use DateTime;
use Exception;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Log;

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
            'rows.columns.taskCards' => function ($q) {
                $q->where('status', 'active')
                    ->with('badge', 'board', 'row', 'column', 'taskFiles', 'comments')
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
        ])->find($this->boardId);
    }
}
