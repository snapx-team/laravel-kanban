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

    public function getTicketsByEmployee() {
        $tasks = Task::select('reporter_id')->orderBy('reporter_id')->get();
        $employees = Employee::orderBy('id')->get();

        $reporters = [];
        // uses the kanban_employees table ONLY. what is kanban members?
        foreach($employees as $employee) {
            array_push($reporters, $employee->role);
            $assArray[$employee->id] = 0;
        }

        foreach($tasks as $task) {
            $assArray[$task->reporter_id] += 1;
        }
        
        return [
            'hits' => array_values($assArray),
            'names' => $reporters
        ];
    }

    public function getCreationByHour() {
        $tasks = Task::get();

        $arr = [];
        foreach($tasks as $task) {
            $date = new DateTime(($task->created_at));
            $dateString = $date->format('G');
            array_push($arr, $dateString);
            // if(array_key_exists($dateString, $arr)) {
            //     $arr[$dateString] += 1;
            // } else {
            //     $arr[$dateString] = 1;
            // }
        }
        return [
            'dates' =>$arr,
        ];
    }

    
}
