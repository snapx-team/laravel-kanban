<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use DateInterval;
use DatePeriod;
use DateTime;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

class MetricsController extends Controller
{
    /**
     * @throws Exception
     */
    public function getBadgeData($start, $end): array
    {
        $tasks = Task::select('badge_id', DB::raw('count(*) as total'))
            ->groupBy('badge_id')
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->get();

        $badgeData = [];
        foreach ($tasks as $task) {
            $badgeData[$task->badge->name] = $task->total;
        }

        return [
            'names' => array_keys($badgeData),
            'hits' => array_values($badgeData),
        ];
    }

    /**
     * @throws Exception
     */
    public function getContractData($start, $end): array
    {
        $tasks = Task::whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->get();

        $ContractData = [];
        foreach ($tasks as $task) {
            foreach ($task->sharedTaskData->erpContracts as $contract) {
                if (array_key_exists($contract->contract_identifier, $ContractData)) {
                    $ContractData[$contract->contract_identifier] += 1;
                } else {
                    $ContractData[$contract->contract_identifier] = 1;
                }
            }
        }

        return [
            'hits' => array_values($ContractData),
            'names' => array_keys($ContractData),
        ];
    }

    /**
     * @throws Exception
     */
    public function getTasksCreatedByEmployee($start, $end): array
    {
        $tasks = Task::select('reporter_id', DB::raw('count(*) as total'))
            ->groupBy('reporter_id')
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->get();

        $reportersData = [];
        if (count($tasks) > 0) {
            foreach ($tasks as $task) {
                $reportersData[$task->reporter->user->full_name] = $task->total;
            }
        }

        return [
            'hits' => array_values($reportersData),
            'names' => array_keys($reportersData),
        ];
    }

    /**
     * @throws Exception
     */

    public function getEstimatedHoursCompletedByEmployees($start, $end): array
    {
        $logs = Log::where('log_type', Log::TYPE_CARD_COMPLETED)
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->where('loggable_type', 'Xguard\LaravelKanban\Models\Task')
            ->orderBy('loggable_id')
            ->latest()
            ->get()
            ->unique('loggable_id');

        $names = [];
        $hits = [];
        foreach ($logs as $log) {
            foreach ($log->loggable->assignedTo as $employee) {
                // check whether card estimate greater than 0 and if original task status is still completed
                if ($log->loggable->time_estimate > 0 && $log->loggable->status === 'completed') {
                    if (array_key_exists($employee['employee']['user']['email'], $hits)) {
                        $hits[$employee['employee']['user']['email']] += $log->loggable->time_estimate;
                    } else {
                        $hits[$employee['employee']['user']['email']] = $log->loggable->time_estimate;
                        array_push($names, $employee['employee']['user']['full_name']);
                    }
                }
            }
        }

        return [
            'hits' => array_values($hits),
            'names' => $names,
        ];
    }

    /**
     * @throws Exception
     */
    public function getPeakHoursTasksCreated($start, $end): array
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
            $dateString = $date->format('G');
            $arr[$dateString] += 1;
        }
        ksort($arr);

        return [
            'hits' => array_values($arr),
            'names' => array_keys($arr),
        ];
    }

    /**
     * @throws Exception
     */
    public function getClosedTasksByAssignedTo($start, $end): array
    {
        $logs = Log::where('log_type', Log::TYPE_CARD_COMPLETED)
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->where('loggable_type', 'Xguard\LaravelKanban\Models\Task')
            ->orderBy('loggable_id')
            ->latest()
            ->get()
            ->unique('loggable_id');

        $names = [];
        $hits = [];
        foreach ($logs as $log) {
            foreach ($log->loggable->assignedTo as $employee) {
                // check if original task status is still completed
                if ($log->loggable->status === 'completed') {
                    if (array_key_exists($employee['employee']['user']['email'], $hits)) {
                        $hits[$employee['employee']['user']['email']] += 1;
                    } else {
                        $hits[$employee['employee']['user']['email']] = 1;
                        array_push($names, $employee['employee']['user']['full_name']);
                    }
                }
            }
        }

        return [
            'hits' => array_values($hits),
            'names' => $names,
        ];
    }

    /**
     * @throws Exception
     */
    public function getClosedTasksByAdmin($start, $end): array
    {
        $logs = Log::with('user')
            ->where('log_type', Log::TYPE_CARD_COMPLETED)
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->where('loggable_type', 'Xguard\LaravelKanban\Models\Task')
            ->orderBy('loggable_id')
            ->latest()
            ->get()
            ->unique('loggable_id');

        $names = [];
        $hits = [];
        foreach ($logs as $log) {
            if (array_key_exists($log->user['email'], $hits)) {
                $hits[$log->user['email']] += 1;
            } else {
                $hits[$log->user['email']] = 1;
                array_push($names, $log->user['full_name']);
            }
        }

        return [
            'hits' => array_values($hits),
            'names' => $names,
        ];
    }


    /**
     * @throws Exception
     */
    public function getAverageTimeToCompletionByBadge($start, $end): array
    {
        $closedLogs = Log::where('log_type', Log::TYPE_CARD_COMPLETED)
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->where('loggable_type', 'Xguard\LaravelKanban\Models\Task')
            ->orderBy('loggable_id')->get();
        $assignedLogs = Log::with(['loggable' => function (MorphTo $morphTo) {
            $morphTo->morphWith([Task::class => ['badge']]);
        }])
            ->where('log_type', Log::TYPE_CARD_ASSIGNED_TO_USER)
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->where('loggable_type', 'Xguard\LaravelKanban\Models\Task')
            ->orderBy('loggable_id')
            ->latest()
            ->get()
            ->unique('loggable_id');

        $names = [];
        $hits = [];
        foreach ($assignedLogs as $assignedLog) {
            foreach ($closedLogs as $key => $closedLog) {
                if ($assignedLog->loggable_id == $closedLog->loggable_id) {
                    $beginning = new DateTime($assignedLog->created_at);
                    $end = new DateTime($closedLog->created_at);
                    $hours = ($end->diff($beginning))->h;
                    if (array_key_exists($assignedLog->loggable->badge_id, $hits)) {
                        array_push($hits[$assignedLog->loggable->badge_id], $hours);
                        unset($closedLogs[$key]);
                    } else {
                        $hits[$assignedLog->loggable->badge_id] = [$hours];
                        array_push($names, $assignedLog->loggable->badge->name);
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
    public function getAverageTimeToCompletionByEmployee($start, $end): array
    {
        $closedLogs = Log::where('log_type', Log::TYPE_CARD_COMPLETED)
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->where('loggable_type', 'Xguard\LaravelKanban\Models\Task')
            ->orderBy('loggable_id')
            ->get();
        $assignedLogs = Log::whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->where('log_type', Log::TYPE_CARD_ASSIGNED_TO_USER)
            ->where('loggable_type', 'Xguard\LaravelKanban\Models\Task')
            ->orderBy('loggable_id')
            ->latest()
            ->get()
            ->unique('loggable_id');

        $names = [];
        $hits = [];
        foreach ($assignedLogs as $assignedLog) {
            foreach ($closedLogs as $key => $closedLog) {
                if ($assignedLog->loggable_id == $closedLog->loggable_id) {
                    $beginning = new DateTime($assignedLog->created_at);
                    $end = new DateTime($closedLog->created_at);
                    $diff = $end->diff($beginning);
                    $hours = $diff->h;
                    $hours = $hours + ($diff->days * 24);

                    foreach ($assignedLog->loggable->assignedTo as $user) {
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
            ->orWhere('log_type', Log::TYPE_CARD_CANCELLED)
            ->whereDate('created_at', '>=', $beginning)
            ->whereDate('created_at', '<=', $ending)
            ->where('loggable_type', 'Xguard\LaravelKanban\Models\Task')
            ->orderBy('loggable_id')
            ->get();
        $createdLogs = Log::with('user')
            ->whereDate('created_at', '>=', $beginning)
            ->whereDate('created_at', '<=', $ending)
            ->where('log_type', Log::TYPE_CARD_CREATED)
            ->where('loggable_type', 'Xguard\LaravelKanban\Models\Task')
            ->orderBy('loggable_id')
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
