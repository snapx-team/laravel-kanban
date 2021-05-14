<?php

namespace Xguard\LaravelKanban\database\seeds;

use Illuminate\Database\Seeder;
use Xguard\LaravelKanban\Models\Employee;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Employee::create([
            'user_id' => 1,
            'role' => 'admin',
        ]);
        Employee::create([
            'user_id' => 2,
            'role' => 'admin',
        ]);
        Employee::create([
            'user_id' => 3,
            'role' => 'admin',
        ]);
    }
}
