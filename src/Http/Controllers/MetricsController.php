<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use DateTime;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Task;

class MetricsController extends Controller
{
    
    public function getBadgeData($start, $end)
    {
        $tasks = Task::with('badge')
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->orderBy('badge_id')
            ->get();


        $names = [];
        $hits = [];
        foreach ($tasks as $task) {
            if(array_key_exists($task->badge_id, $hits)){
                $hits[$task->badge_id] += 1;
            } else {
                $hits[$task->badge_id] = 1;
                array_push($names, $task->badge->name);
            }
        }

        return [
            'names' => $names,
            'hits' => array_values($hits),
        ];
    }

    public function getJobSiteData($start, $end)
    {
        $tasks = Task::with('jobSite')
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->orderBy('erp_job_site_id')
            ->get();

        $jobsiteNames = [];
        $jobsiteCounts = [];
        foreach($tasks as $task) {
            if($task->erp_job_site_id !== null && $task->jobsite !== null) {
                if(array_key_exists($task->erp_job_site_id, $jobsiteCounts)){
                    $jobsiteCounts[$task->erp_job_site_id] += 1;
                } else {
                    $jobsiteCounts[$task->erp_job_site_id] = 1;
                    array_push($jobsiteNames, $task->jobsite->name);
                }
            }
        }

        return [
            'hits' => array_values($jobsiteCounts),
            'names' => $jobsiteNames,
        ];
    }

    public function getTicketsByEmployee($start, $end)
    {
        $tasks = Task::select('reporter_id')
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->orderBy('reporter_id')
            ->get();

        $employees = Employee::with('user')->orderBy('id')->get();

        $reporters = [];
        $assArray = array();
        if(count($tasks) > 0) {
            $assArray[$tasks[0]->reporter_id] = 0;
            foreach($employees as $employee) {
                array_push($reporters, $employee->user->full_name);
                if(!array_key_exists($employee->id, $assArray)) {
                    $assArray[$employee->id] = 0;
                }
            }

            foreach($tasks as $task) {
                $assArray[$task->reporter_id] += 1;
            }
        }
        
        return [
            'hits' => array_values($assArray),
            'names' => $reporters,
            'employees' => $employees,
            'test' => $assArray
        ];
    }

    public function getCreationByHour($start, $end)
    {
        $tasks = Task::whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->get();

        $arr = [];
        for ($i = 0; $i < 24; $i++) {
            $arr[strval($i)] = 0;
        }

        foreach($tasks as $task) {
            $date = (new DateTime(($task->created_at)))->modify('-4 hours');;
            $dateString = $date->format('G');
            $arr[$dateString] += 1;
        }
        ksort($arr);

        return [
            'hits' => array_values($arr),
            'names' => array_keys($arr),
        ];
    }

    public function getClosedTasksByEmployee($start, $end)
    {
        $logs = Log::with('user')->where('log_type', Log::TYPE_CARD_CLOSED)
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->orderBy('user_id')
            ->get();

        $names = [];
        $hits = [];
        foreach($logs as $log) {
            if(array_key_exists($log->user_id, $hits)){
                $hits[$log->user_id] += 1;
            } else {
                $hits[$log->user_id] = 1;
                array_push($names, $log->user->full_name);
            }
        }

        return [
            'hits' => array_values($hits),
            'names' => $names,
        ];
    }


    public function getDelayByBadge($start, $end)
    {
        $closedLogs = Log::where('log_type', Log::TYPE_CARD_CLOSED)
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->orderBy('task_id')->get();
        $assignedLogs = Log::with('badge')
            ->where('log_type', Log::TYPE_CARD_ASSIGNED)
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->orderBy('task_id')
            ->get();

        $names = [];
        $hits = [];
        foreach($assignedLogs as $assignedLog) {
            foreach ($closedLogs as $key => $closedLog) {
                if ($assignedLog->task_id == $closedLog->task_id){
                    $beginning = new DateTime($assignedLog->created_at);
                    $end = new DateTime($closedLog->created_at);
                    $hours = ($end->diff($beginning))->h;
                    if(array_key_exists($assignedLog->badge_id, $hits)){
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
        foreach($hits as $value) {
            array_push($averages, array_sum($value)/count($value));
        }

        return [
            'names' => $names,
            'hits' => $averages,
        ];
    }

    public function getDelayByEmployee($start, $end)
    {
        $closedLogs = Log::where('log_type', Log::TYPE_CARD_CLOSED)
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->orderBy('task_id')->get();
        $assignedLogs = Log::with('user')
            ->whereDate('created_at', '>=', new DateTime($start))
            ->whereDate('created_at', '<=', new DateTime($end))
            ->where('log_type', Log::TYPE_CARD_ASSIGNED)
            ->orderBy('task_id')
            ->get();

        $names = [];
        $hits = [];
        foreach($assignedLogs as $assignedLog) {
            foreach ($closedLogs as $key => $closedLog) {
                if ($assignedLog->task_id == $closedLog->task_id){
                    $beginning = new DateTime($assignedLog->created_at);
                    $end = new DateTime($closedLog->created_at);
                    $hours = ($end->diff($beginning))->h;
                    if(array_key_exists($assignedLog->user_id, $hits)){
                        array_push($hits[$assignedLog->user_id], $hours);
                        unset($closedLogs[$key]);
                    } else {
                        $hits[$assignedLog->user_id] = [$hours];
                        array_push($names, $assignedLog->user->full_name);
                    }
                }
            }
        }

        $averages = [];
        foreach($hits as $value) {
            array_push($averages, array_sum($value)/count($value));
        }

        return [
            'names' => $names,
            'hits' => $averages,
        ];
    }
    
}
