<?php

namespace Xguard\LaravelKanban\Repositories;

use Xguard\LaravelKanban\Models\Log;

class LogsRepository
{
    public static function create(array $payload): Log
    {
        return Log::create($payload);
    }
}
