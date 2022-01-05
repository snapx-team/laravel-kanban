<?php

namespace Xguard\LaravelKanban\QueryBuilders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method User|null first()
 */
class TasksQueryBuilder extends Builder
{
    public function whereSearchText(string $searchText)
    {
        $extractedLetters = preg_replace("/[^a-zA-Z]/", "", $searchText);
        $extractedNumbers = abs(filter_var($searchText, FILTER_SANITIZE_NUMBER_INT));

        if (preg_match("/[a-zA-Z]{1,3}-\d{1,7}/", $searchText)) {
            return $this->whereHas('board', function ($q) use ($extractedLetters) {
                $q->where('name', 'like', $extractedLetters."%");
            })->where(function ($q) use ($extractedNumbers, $searchText) {
                $q->where('id', 'like', $extractedNumbers."%")
                    ->orWhere('name', 'like', "%{$searchText}%");
            });
        } elseif ($extractedLetters != "") {
            return $this->where(function ($q) use ($extractedLetters, $searchText) {
                $q->whereHas('board', function ($q) use ($extractedLetters) {
                    $q->where('name', 'like', $extractedLetters."%");
                })->orWhere('name', 'like', "%{$searchText}%");
            });
        } elseif ($extractedNumbers != 0) {
            return $this->where(function ($q) use ($extractedNumbers, $searchText) {
                $q->where('id', 'like', $extractedNumbers."%")
                    ->orWhere('name', 'like', "%{$searchText}%");
            });
        } else {
            return $this;
        }
    }

    public function withTaskData(): TasksQueryBuilder
    {
        return $this->with('badge', 'row', 'column', 'board', 'sharedTaskData', 'taskFiles', 'comments')
            ->with([
                'assignedTo.employee.user' => function ($q) {
                    $q->select(['id', 'first_name', 'last_name']);
                }
            ])
            ->with([
                'reporter.user' => function ($q) {
                    $q->select(['id', 'first_name', 'last_name']);
                }
            ])
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
            ]);
    }
}
