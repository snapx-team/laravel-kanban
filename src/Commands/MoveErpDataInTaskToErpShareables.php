<?php

namespace Xguard\LaravelKanban\Commands;

use Illuminate\Console\Command;
use Xguard\LaravelKanban\Models\ErpShareables;
use Xguard\LaravelKanban\Models\Task;

class MoveErpDataInTaskToErpShareables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kanban:erp-shareables-fix';

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
            if ($task->erp_contract_id !== null) {
                ErpShareables::firstOrCreate([
                    'shareable_type' => 'contract',
                    'shareable_id' => $task->erp_contract_id,
                    'shared_task_data_id' => $task->shared_task_data_id,
                ]);
            }

            if ($task->erp_employee_id !== null) {
                ErpShareables::firstOrCreate([
                    'shareable_type' => 'user',
                    'shareable_id' => $task->erp_employee_id,
                    'shared_task_data_id' => $task->shared_task_data_id,
                ]);
            }
        }
    }
}
