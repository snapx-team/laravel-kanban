<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

class LogController extends Controller
{

    public function getLogs($id)
    {
        // this will also get all logs of related tasks
        $sharedTaskDataId = Task::find($id)->shared_task_data_id;
        $allAssociatedTasks = Task::where('shared_task_data_id', '=', $sharedTaskDataId)->pluck('id')->toArray();
        return Log::with(['user', 'loggable' => function (MorphTo $morphTo) {
            $morphTo->morphWith([Task::class => ['board', 'badge']]);
            }])
            ->where('loggable_type', 'Xguard\LaravelKanban\Models\Task')
            ->whereIn('loggable_id', $allAssociatedTasks)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
