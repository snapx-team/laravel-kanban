<?php

namespace Xguard\LaravelKanban\Commands;

use Illuminate\Console\Command;
use Xguard\LaravelKanban\Models\Employee;

class DeleteKanbanEmployeesWithDeletedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kanban:delete-employees-with-no-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes Kanban employees for which ERP users were deleted';

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
        Employee::whereDoesntHave('user')->delete();
    }
}
