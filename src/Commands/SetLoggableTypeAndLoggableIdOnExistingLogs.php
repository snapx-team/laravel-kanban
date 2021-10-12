<?php

namespace Xguard\LaravelKanban\Commands;

use Illuminate\Console\Command;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;

class SetLoggableTypeAndLoggableIdOnExistingLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kanban:set-polymorphic-log-type-and-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the loggable type and id of kanban_logs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $logs = Log::all();
        foreach ($logs as $log) {
            if ($log->task_id && $log->log_type != 90) {
                $log->update([
                    'loggable_type' => 'Xguard\LaravelKanban\Models\Task',
                    'loggable_id' => $log->task_id
                ]);
            } else if ($log->board_id) {
                $log->update([
                    'loggable_type' => 'Xguard\LaravelKanban\Models\Board',
                    'loggable_id' => $log->board_id
                ]);
            } else if ($log->badge_id) {
                $log->update([
                    'loggable_type' => 'Xguard\LaravelKanban\Models\Badge',
                    'loggable_id' => $log->task_id
                ]);
            } else {
                if ($log->log_type == 1 || $log->log_type == 3) {
                    if (!$log->loggable_type) {
                        $full_name = substr($log->description, strpos($log->description, '[') + 1, -1);
                        $name_parts = preg_split('/\s+/', $full_name);
                        $employee = Employee::whereHas('user', function ($query) use ($name_parts) {
                            $query->where('first_name', 'like', $name_parts[0].'%')->where('last_name', 'like', '%'.$name_parts[count($name_parts)-1].'%');
                        })->first();
    
                        if ($employee) {
                            $log->update([
                                'loggable_type' => 'Xguard\LaravelKanban\Models\Employee',
                                'loggable_id' => $employee->id
                            ]);
                        }
                    }
                }
            }
        }
    }
}
