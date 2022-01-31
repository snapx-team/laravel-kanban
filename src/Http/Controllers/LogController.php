<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Xguard\LaravelKanban\Repositories\LogsRepository;

class LogController extends Controller
{
    public function getLogs($id): LengthAwarePaginator
    {
        return LogsRepository::getLogs(intval($id));
    }
}
