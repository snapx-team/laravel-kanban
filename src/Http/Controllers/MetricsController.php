<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

class MetricsController extends Controller
{

    public function getBadgeData($start, $end): array
    {
        $tasks = Task::with('badge')
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->get();


        $full = [];
        foreach ($tasks as $task) {
            if (array_key_exists($task->badge->name, $full)) {
                $full[$task->badge->name] += 1;
            } else {
                $full[$task->badge->name] = 1;
            }
        }

        return [
            'names' => array_keys($full),
            'hits' => array_values($full),
        ];
    }

    public function getContractData($start, $end): array
    {
        $tasks = Task::with(['sharedTaskData' => function ($q) {
                $q->with(['erpContracts' => function ($q) {
                    $q->select(['contracts.id', 'contract_identifier']);
                }])->with(['erpEmployees' => function ($q) {
                    $q->select(['users.id', 'first_name', 'last_name']);
                }]);
        }])
        ->whereDate('created_at', '>=', new DateTime($start))
        ->whereDate('created_at', '<=', new DateTime($end))
        ->get();

        $ContractCounts = [];
        foreach ($tasks as $task) {
            foreach ($task->sharedTaskData->erpContracts as $contract) {
                if (array_key_exists($contract->contract_identifier, $ContractCounts)) {
                    $ContractCounts[$contract->contract_identifier] += 1;
                } else {
                    $ContractCounts[$contract->contract_identifier] = 1;
                }
            }
        }

        return [
            'hits' => array_values($ContractCounts),
            'names' => array_keys($ContractCounts),
        ];
    }

    public function getTicketsByEmployee($start, $end): array
    {
        $tasks = Task::with('reporter')
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->orderBy('reporter_id')
            ->get();

        $employees = Employee::with('user')->orderBy('id')->get();

        $reporters = [];
        $assArray = array();
        if (count($tasks) > 0) {
            foreach ($employees as $employee) {
                array_push($reporters, $employee->user->full_name);
                if (!array_key_exists($employee->user->email, $assArray)) {
                    $assArray[$employee->user->email] = 0;
                }
            }
            foreach ($tasks as $task) {
                $assArray[$task->reporter->email] += 1;
            }
        }

        return [
            'hits' => array_values($assArray),
            'names' => $reporters,
        ];
    }

    public function getCreationByHour($start, $end): array
    {
        $tasks = Task::whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->get();

        $arr = [];
        for ($i = 0; $i < 24; $i++) {
            $arr[strval($i)] = 0;
        }

        foreach ($tasks as $task) {
            $date = (new DateTime(($task->created_at)))->modify('-4 hours');
            ;
            $dateString = $date->format('G');
            $arr[$dateString] += 1;
        }
        ksort($arr);

        return [
            'hits' => array_values($arr),
            'names' => array_keys($arr),
        ];
    }

    public function getClosedTasksByEmployee($start, $end): array
    {
        $logs = Log::with('user')->where('log_type', Log::TYPE_CARD_COMPLETED)
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->orderBy('user_id')
            ->get();

        $names = [];
        $hits = [];
        foreach ($logs as $log) {
            if (array_key_exists($log->user->email, $hits)) {
                $hits[$log->user->email] += 1;
            } else {
                $hits[$log->user->email] = 1;
                array_push($names, $log->user->full_name);
            }
        }

        return [
            'hits' => array_values($hits),
            'names' => $names,
        ];
    }


    public function getDelayByBadge($start, $end): array
    {
        $closedLogs = Log::where('log_type', Log::TYPE_CARD_COMPLETED)
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->orderBy('task_id')->get();
        $assignedLogs = Log::with('badge')
            ->where('log_type', Log::TYPE_CARD_ASSIGNED_TO_USER)
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->orderBy('task_id')
            ->latest()
            ->get()
            ->unique('task_id');

        $names = [];
        $hits = [];
        foreach ($assignedLogs as $assignedLog) {
            foreach ($closedLogs as $key => $closedLog) {
                if ($assignedLog->task_id == $closedLog->task_id) {
                    $beginning = new DateTime($assignedLog->created_at);
                    $end = new DateTime($closedLog->created_at);
                    $hours = ($end->diff($beginning))->h;
                    if (array_key_exists($assignedLog->badge_id, $hits)) {
                        array_push($hits[$assignedLog->badge_id], $hours);
                        unset($closedLogs[$key]);
                    } else {
                        $hits[$assignedLog->badge_id] = [$hours];
                        array_push($names, $assignedLog->badge->name);
                    }
                }
            }
        }

        $averages = [];
        foreach ($hits as $value) {
            array_push($averages, sprintf("%.2f", array_sum($value) / count($value)));
        }

        return [
            'names' => $names,
            'hits' => $averages,
        ];
    }

    /**
     * @throws Exception
     */
    public function getDelayByEmployee($start, $end): array
    {
        $closedLogs = Log::where('log_type', Log::TYPE_CARD_COMPLETED)
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->orderBy('task_id')
            ->get();
        $assignedLogs = Log::with('user')
            ->with(['task.assignedTo.employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->where('log_type', Log::TYPE_CARD_ASSIGNED_TO_USER)
            ->orderBy('task_id')
            ->latest()
            ->get()
            ->unique('task_id');

        $names = [];
        $hits = [];
        foreach ($assignedLogs as $assignedLog) {
            foreach ($closedLogs as $key => $closedLog) {
                if ($assignedLog->task_id == $closedLog->task_id) {
                    $beginning = new DateTime($assignedLog->created_at);
                    $end = new DateTime($closedLog->created_at);
                    $diff = $end->diff($beginning);
                    $hours = $diff->h;
                    $hours = $hours + ($diff->days*24);

                    foreach ($assignedLog->task->assignedTo as $user) {
                        if (array_key_exists($user->employee->user->id, $hits)) {
                            array_push($hits[$user->employee->user->id], $hours);
                            unset($closedLogs[$key]);
                        } else {
                            $hits[$user->employee->user->id] = [$hours];
                            array_push($names, $user->employee->user->full_name);
                        }
                    }
                }
            }
        }

        $averages = [];
        foreach ($hits as $value) {
            array_push($averages, sprintf("%.2f", array_sum($value) / count($value)));
        }

        return [
            'names' => $names,
            'hits' => $averages,
        ];
    }

    /**
     * @throws Exception
     */
    public function getCreatedVsResolved($start, $end): array
    {
        $beginning = new DateTime($start);
        $ending = new DateTime($end);
        $resolvedLogs = Log::where('log_type', Log::TYPE_CARD_COMPLETED)
            ->orWhere('log_type', Log::TYPE_CARD_CANCELED)
            ->whereDate('created_at', '>=', $beginning)
            ->whereDate('created_at', '<=', $ending)
            ->orderBy('task_id')
            ->get();
        $createdLogs = Log::with('user')
            ->whereDate('created_at', '>=', $beginning)
            ->whereDate('created_at', '<=', $ending)
            ->where('log_type', Log::TYPE_CARD_CREATED)
            ->orderBy('task_id')
            ->get();

        $period = new DatePeriod($beginning, new DateInterval('P1D'), $ending);

        $series = [
            (object)[
                'name' => 'Created',
                'data' => []
            ],
            (object)[
                'name' => 'Resolved',
                'data' => []
            ]
        ];

        $categories = [];
        foreach ($period as $day) {
            $currentDay = $day->format("m/d");
            array_push($categories, $currentDay);
            $count = 0;
            foreach ($createdLogs as $createdLog) {
                $tempDay = new DateTime($createdLog->created_at);
                $createdDay = $tempDay->format("m/d");
                if ($currentDay == $createdDay) {
                    $count += 1;
                }
            }
            array_push($series[0]->data, $count);

            $count = 0;
            foreach ($resolvedLogs as $resolvedLog) {
                $tempDay = new DateTime($resolvedLog->created_at);
                $resolvedDay = $tempDay->format("m/d");
                if ($currentDay == $resolvedDay) {
                    $count += 1;
                }
            }
            array_push($series[1]->data, $count);
        }

        return [
            'series' => $series,
            'categories' => $categories,
        ];
    }
}
