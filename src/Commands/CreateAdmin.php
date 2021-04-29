<?php

namespace Xguard\LaravelKanban\Commands;

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
        $name = $this->ask('First and last name:');
        $email = $this->ask('ERP email:');
        $phone = $this->ask('Phone number (with country code:)');
        $isActive = $this->confirm('Is this employee available to receive calls?');

        Employee::create([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'role' => 'admin',
            'is_active' => $isActive,
        ]);
    }
}
