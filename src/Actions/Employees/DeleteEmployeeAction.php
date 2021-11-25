<?php

namespace Xguard\LaravelKanban\Actions\Employees;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;

class DeleteEmployeeAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'employeeId' => ['required', 'integer', 'gt:0'],
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();
            $employee = Employee::findOrFail($this->employeeId);
            $employee->delete();

            Log::createLog(
                Auth::user()->id,
                Log::TYPE_EMPLOYEE_DELETED,
                'Deleted employee [' . $employee->user->full_name . ']',
                $employee->id,
                $employee->id,
                'Xguard\LaravelKanban\Models\Employee'
            );
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
