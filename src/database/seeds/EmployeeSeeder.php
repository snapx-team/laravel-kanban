<?php

namespace Xguard\LaravelKanban\database\seeds;

use Illuminate\Database\Seeder;
use Xguard\PhoneScheduler\Models\Employee;

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
            'name' => 'admin',
            'email' => 'system-admin@test.com',
            'phone' => '15556667777',
            'role' => 'admin',
            'is_active' => true,
        ]);
        Employee::create([
            'name' => 'employee',
            'email' => 'siamak@snapx.com',
            'phone' => '15145590578',
            'role' => 'employee',
            'is_active' => true,
        ]);
    }
}
