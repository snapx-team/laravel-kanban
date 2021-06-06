<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Xguard\LaravelKanban\Models\Employee;

class EmployeeController extends Controller
{
    public function createEmployees(Request $request)
    {
        $employeeData = $request->all();

        try {

            foreach ($employeeData['selectedUsers'] as $user) {

                $employee = Employee::updateOrCreate(
                    ['user_id' => $user['id']],
                    ['role' => $employeeData['role'],]
                );

            }


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
            $employee = Employee::find($id);
            $employee->delete();
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

    public function getAllUsers()
    {
        return User::orderBy('first_name')->get();
    }
}
