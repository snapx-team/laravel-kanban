<?php

namespace Xguard\LaravelKanban\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\ErpShareables;
use Xguard\LaravelKanban\Models\Task;

class ReplaceAllReporterIdsFromUserToEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kanban:fix-reporter-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move ERP data from task to ERP Shareables';

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
        $tasks = Task::all();

        foreach ($tasks as $task) {
            $employeeId = Employee::where('user_id', $task->reporter_id)->first()->id;

            $task->reporter_id = $employeeId;
            $task->save();
        }
    }
}
