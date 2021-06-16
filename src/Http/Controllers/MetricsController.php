<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use DateTime;
use Xguard\LaravelKanban\Models\Employee;
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
        $tasks = Task::orderBy('badge_id')->get();

    }

    public function getTicketsByEmployee() {
        $tasks = Task::select('reporter_id')->orderBy('reporter_id')->get();
        $employees = Employee::with('user')->orderBy('id')->get();

        $reporters = [];
        $assArray = array();
        $assArray[1] = 0;
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
            $date = new DateTime(($task->created_at));
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

    
}
