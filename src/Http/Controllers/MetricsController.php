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
    
    public function getBadgeData()
    {
        $tasks = Task::orderBy('badge_id')->get();

        $badges = Badge::orderBy('id')->get();

        $badgeNames = [];
        foreach ($badges as $badge) {
            $badgeHits[$badge->id] = 0;
            array_push($badgeNames, $badge->name);
        }

        foreach ($tasks as $task) {
            $badgeHits[$task->badge_id] += 1;
        }

        return [
            'names' => $badgeNames,
            'hits' => array_values($badgeHits)
        ];
    }

    public function getJobSiteData() {
        $tasks = Task::with('jobSite')->orderBy('erp_job_site_id')->get();

        $jobsiteNames = [];
        $jobsiteCounts = [];
        foreach($tasks as $task) {
            if($task->erp_job_site_id !== null) {
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

    public function getTicketsByEmployee() {
        $tasks = Task::select('reporter_id')->orderBy('reporter_id')->get();
        $employees = Employee::with('user')->orderBy('id')->get();

        $reporters = [];
        $assArray = array();
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
        
        return [
            'hits' => array_values($assArray),
            'names' => $reporters,
            'employees' => $employees,
            'test' => $assArray
        ];
    }

    public function getCreationByHour() {
        $tasks = Task::get();

        $arr = [];
        foreach($tasks as $task) {
            $date = (new DateTime(($task->created_at)))->modify('-4 hours');;
            $dateString = $date->format('G');
            if(array_key_exists($dateString, $arr)) {
                $arr[$dateString] += 1;
            } else {
                $arr[$dateString] = 1;
            }
        }
        ksort($arr);
        return [
            'hits' => array_values($arr),
            'names' => array_keys($arr),
        ];
    }

    public function getClosedTasksByEmployee() {
        $logs = Log::with('user')->where('log_type', '22')->orderBy('user_id')->get();

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

    public function getDelayByBadge() {
        $logs = Log::with('badge', 'task')->where('log_type', '22')->orderBy('badge_id')->get();

        $names = [];
        $hits = [];
        foreach($logs as $log){
            $beginning = new DateTime($log->task->created_at);
            $end = new DateTime($log->created_at);
            $hours = ($end->diff($beginning))->h;
            if(array_key_exists($log->badge_id, $hits)){
                array_push($hits[$log->badge_id], $hours);
            } else {
                $hits[$log->badge_id] = [$hours];
                array_push($names, $log->badge->name);
            }
        }

        $averages = [];
        foreach($hits as $value) {
            array_push($averages, array_sum($value)/count($value));
        }

        return [
            'names' => $names,
            'hits' => $averages
        ];
    }

    public function getDelayByEmployee() {
        $closedLogs = Log::where('log_type', '22')->orderBy('task_id')->get();
        $assignedLogs = Log::with('user')->where('log_type', '23')->orderBy('task_id')->get();

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
