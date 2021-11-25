<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Actions\Employees\CreateOrUpdateEmployeeAction;
use Xguard\LaravelKanban\Actions\Employees\DeleteEmployeeAction;

class EmployeeController extends Controller
{
    public function createEmployees(Request $request)
    {
        $employeeData = $request->all();

        try {
            app(CreateOrUpdateEmployeeAction::class)->fill([
                "selectedUsers" => $employeeData['selectedUsers'],
                "role" => $employeeData['role'],
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function deleteEmployee($id)
    {
        try {
            app(DeleteEmployeeAction::class)->fill([
                'employeeId' => $id
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function getEmployees()
    {
        return Employee::with('user')->get();
    }
}
