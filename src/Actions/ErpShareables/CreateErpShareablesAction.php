<?php

namespace Xguard\LaravelKanban\Actions\ErpShareables;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\SharedTaskData;

class CreateErpShareablesAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "description" => ['required', 'string'],
            "erpEmployees" => ['present', 'array'],
            "erpContracts" => ['present', 'array'],
        ];
    }

    public function messages()
    {
        return [
            'description.required' => 'Task description is required',
        ];
    }
    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $sharedTaskData = SharedTaskData::create(['description' => $this->description]);

        $erpEmployeesArray = [];
        $erpContractsArray = [];

        foreach ($this->erpEmployees as $erpEmployee) {
            $erpEmployeesArray[$erpEmployee['id']] = ['shareable_type' => 'user'];
        }

        foreach ($this->erpContracts as $erpContract) {
            $erpContractsArray[$erpContract['id']] = ['shareable_type' => 'contract'];
        }

        $sharedTaskData->erpContracts()->sync($erpContractsArray);
        $sharedTaskData->erpEmployees()->sync($erpEmployeesArray);

        return $sharedTaskData;
    }
}
