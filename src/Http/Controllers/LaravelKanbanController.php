<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use DateTime;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Board;

class LaravelKanbanController extends Controller
{
    public function getIndex()
    {

        return view('Xguard\LaravelKanban::index');
    }

    public function getkanbanData($id)
    {
        $phoneLine = Board::with('members.employee', 'rows.columns.employeeCards.employee')->find($id);
        return $phoneLine;
    }

    public function getDashboardData()
    {
        $employees = Employee::orderBy('name')->get();
        $phoneLine = Board::orderBy('name')->with('members')->get();
        return [
            'employees' => $employees,
            'phoneLines' => $phoneLine
        ];
    }

    public function getFormattedData($id)
    {
        $kanbanData = Board::with('members.employee', 'rows.columns.employeeCards.employee')->find($id);

        $rows = [];

        foreach ($kanbanData['rows'] as $row) {
            $columns = [];
            foreach ($row['columns'] as $column) {
                $employeeCards = [];
                $employeeIndex = 1;
                foreach ($column['employeeCards'] as $employeeCard) {
                    if ((int)$employeeCard['employee']['is_active']) {
                        $employeeCardData = ['index' => $employeeIndex, 'phone' => $employeeCard['employee']['phone'], 'name' => $employeeCard['employee']['name'],];
                        array_push($employeeCards, $employeeCardData);
                        $employeeIndex++;
                    }
                }
                $columnData = ['timespan' => $column['name'], 'start' => $column['shift_start'], 'end' => $column['shift_end'], 'employee' => $employeeCards,];
                array_push($columns, $columnData);
            }
            $rowData = ['day' => $row['name'], 'shifts' => $columns,];
            array_push($rows, $rowData);
        }
        return json_encode($rows);
    }

    public function getAvailableAgent($id, $level)
    {
        $level--; // we start at index 0
        $kanbanData = Board::with('members.employee', 'rows.columns.employeeCards.employee')->find($id);

        date_default_timezone_set('America/Montreal');

        $dayOfWeek = date("l");
        $currentTime = date('h:i a');

        foreach ($kanbanData['rows'] as $row) {

            if ($row['name'] === $dayOfWeek) {

                foreach ($row['columns'] as $column) {
                    $now = DateTime::createFromFormat('h:i a', $currentTime);
                    $start = DateTime::createFromFormat('h:i a', $column['shift_start']);
                    $end = DateTime::createFromFormat('h:i a', $column['shift_end']);

                    if ($start > $end) $end->modify('+1 day');
                    if ($start <= $now && $now <= $end || $start <= $now->modify('+1 day') && $now <= $end) {
                        try {

                            $filtered = $column->employeeCards->filter(function ($item) {
                                return data_get($item->employee, 'is_active') === 1;
                            });

                            $phone = $filtered->values()->get($level)->employee->phone;
                            $name = $filtered->values()->get($level)->employee->name;
                        } catch (\Exception $e) {
                            return [];
                        }
                        return ['name' => $name, 'phone' => $phone,];
                    }
                }
            }
        }
        return [];
    }
}
