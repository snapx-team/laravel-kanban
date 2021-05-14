<?php

namespace Xguard\LaravelKanban\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Xguard\LaravelKanban\Models\Employee;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kanban:create-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Admin';

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
        $email = $this->ask('ERP email:');
        $user = User::where('email',$email) -> first();

        Employee::create([
            'user_id' => $user->id,
            'role' => 'admin',
        ]);
    }
}
