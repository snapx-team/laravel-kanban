<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Employee;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function createEmployees(Request $request)
    {
        $employeeData = $request->all();

        try {
            foreach ($employeeData['selectedUsers'] as $user) {

                $employee = Employee::with('user')->updateOrCreate(
                    ['user_id' => $user['id']],
                    ['role' => $employeeData['role'],]
                );

                Log::createLog(
                    Auth::user()->id,
                    Log::TYPE_EMPLOYEE_CREATED,
                    'Added employee <' . $employee->user->full_name . '>',
                    null,
                    null,
                    null,
                    $employee->user_id,
                    null
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
            $employee = Employee::with('user')->get()->find($id);
            $employee->delete();

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_EMPLOYEE_DELETED,
                'Deleted employee <' . $employee->user->full_name . '>',
                null,
                null,
                null,
                $employee->user_id,
                null
            );
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
