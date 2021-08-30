<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;

use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

class LogController extends Controller
{

    public function getLogs($id)
    {
        $sharedTaskDataId = Task::find($id)->shared_task_data_id;
        $allAssociatedTasks = Task::where('shared_task_data_id', '=', $sharedTaskDataId)->pluck('id')->toArray();
        return Log::with('badge', 'board', 'user', 'erpEmployee', 'erpJobSite')->whereIn('task_id', $allAssociatedTasks)->orderBy('created_at', 'desc')->get();
    }
}
