<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Xguard\LaravelKanban\Models\Employee;

class EmployeeController extends Controller
{
    public function createEmployee(Request $request)
    {
        $rules = [
            'name' => 'required|unique:kanban_employees,name,' . $request->input('id') . ',id',
            'phone' => 'required|unique:kanban_employees,phone,' . $request->input('id') . ',id|digits_between:9,11',
            'email' => 'required|unique:kanban_employees,email,' . $request->input('id') . ',id',
            'role' => 'required',
            'is_active' => 'required',
        ];

        $customMessages = [
            'name.required' => 'Employee name is required.',
            'name.unique' => 'Employee name must be unique.',
            'phone.unique' => 'Phone number already exists.',
            'phone.required' => 'Phone number is required.',
            'phone.digits_between' => 'Phone number must be between 9-11 digits.',
            'is_active.required' => 'Employee status must be set'
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response([
                'success' => 'false',
                'message' => implode(' ', $validator->messages()->all()),
            ], 400);
        }

        try {
            if ($request->filled('id')) {

                $employee = Employee::where('id', $request->input('id'))
                    ->update(['name' => $request->input('name'),
                        'phone' => $request->input('phone'),
                        'email' => $request->input('email'),
                        'role' => $request->input('role'),
                        'is_active' => $request->input('is_active'),
                    ]);
            } else {
                $employee = Employee::create([
                    'name' => $request->input('name'),
                    'phone' => $request->input('phone'),
                    'email' => $request->input('email'),
                    'role' => $request->input('role'),
                    'is_active' => $request->input('is_active'),
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
        return Employee::orderBy('name')->get();
    }
}
