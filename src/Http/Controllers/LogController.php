<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;

use Xguard\LaravelKanban\Models\Log;

class LogController extends Controller
{

    public function getLogs($searchTerm)
    {
        return Log::with('badge', 'board', 'user', 'erpEmployee', 'erpJobSite')->where('task_id', $searchTerm)->get();
    }
}
