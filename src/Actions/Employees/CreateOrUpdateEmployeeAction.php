<?php

namespace Xguard\LaravelKanban\Actions\Employees;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Illuminate\Support\Facades\Auth;

class CreateOrUpdateEmployeeAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "selectedUsers" => ['present', 'array'],
            "role" => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'selectedUsers.present' => 'No selected users',
            'role.required' => 'Employee role is required',
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
            foreach ($this->selectedUsers as $user) {
                $employee = Employee::updateOrCreate(
                    ['user_id' => $user['id']],
                    ['role' => $this->role]
                );
    
                if ($employee->wasRecentlyCreated) {
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_EMPLOYEE_CREATED,
                        'Added employee [' . $employee->user->full_name . ']',
                        $employee->id,
                        $employee->id,
                        'Xguard\LaravelKanban\Models\Employee'
                    );
                } else {
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_EMPLOYEE_UPDATED,
                        'Updated employee [' . $employee->user->full_name . ']',
                        $employee->id,
                        $employee->id,
                        'Xguard\LaravelKanban\Models\Employee'
                    );
                }
            }
            \DB::commit();
            return $employee;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
