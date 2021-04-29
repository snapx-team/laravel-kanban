<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xguard\LaravelKanban\Models\EmployeeCard;

class EmployeeCardController extends Controller
{
    public function createEmployeeCards(Request $request)
    {
        $employeeCards = $request->all();
        $maxIndex = EmployeeCard::where('column_id', $employeeCards['columnId'])->max('index');

        try {
            foreach ($employeeCards['employeesSelected'] as $employeeCard) {
                $maxIndex++;
                EmployeeCard::create([
                    'index' => $maxIndex,
                    'employee_id' => $employeeCard['employee']['id'],
                    'column_id' => $employeeCards['columnId'],
                    'member_id' => $employeeCard['id'],
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

    public function getEmployeeCardsByColumn($id)
    {
        return EmployeeCard::where('column_id', $id)->with('employee')->get();
    }

    public function updateEmployeeCardIndexes(Request $request)
    {
        $employeeCards = $request->all();
        $newIndex = 0;
        try {
            foreach ($employeeCards as $employeeCard) {
                $newIndex++;
                EmployeeCard::find($employeeCard['id'])->update(['index' => $newIndex]);
            }
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function updateEmployeeCardColumnId($columnId, $employeeCardId)
    {
        try {
            EmployeeCard::find($employeeCardId)->update(['column_id' => $columnId]);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function deleteEmployeeCard($id)
    {
        try {
            $employeeCard = EmployeeCard::find($id);
            $employeeCard->delete();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

}
