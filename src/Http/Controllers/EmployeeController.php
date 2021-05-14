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

        try {
            if ($request->filled('id')) {

                $employee = Employee::where('id', $request->input('id'))
                    ->update(['role' => $request->input('role')]);
            } else {
                $employee = Employee::create([
                    'user_id' => $request->input('name'),
                    'role' => $request->input('role'),
                ]);
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
