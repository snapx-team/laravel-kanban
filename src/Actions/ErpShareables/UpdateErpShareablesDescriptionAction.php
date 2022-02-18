<?php

namespace Xguard\LaravelKanban\Actions\ErpShareables;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\SharedTaskData;

class UpdateErpShareablesDescriptionAction extends Action
{
    const DESCRIPTION = "description";
    const SHARED_TASK_DATA_ID = "sharedTaskDataId";
    const ERP_EMPLOYEES = "erpEmployees";
    const ERP_CONTRACTS = "erpContracts";

    public function rules(): array
    {
        return [
            self::DESCRIPTION => ['required', 'string'],
            self::SHARED_TASK_DATA_ID => ['required', 'integer', 'gt:0'],
            self::ERP_EMPLOYEES => ['present', 'array'],
            self::ERP_CONTRACTS => ['present', 'array'],
        ];
    }

    public function messages()
    {
        return [
            'description.required' => 'Task description is required',
        ];
    }

    public function handle()
    {
        $sharedTaskData = SharedTaskData::where(SharedTaskData::ID, $this->sharedTaskDataId)->first();
        $sharedTaskData->update([SharedTaskData::DESCRIPTION => $this->description]);

        $erpEmployeesArray = [];
        $erpContractsArray = [];

        foreach ($this->erpEmployees as $erpEmployee) {
            $erpEmployeesArray[$erpEmployee['id']] = [SharedTaskData::SHAREABLE_TYPE => SharedTaskData::USER];
        }


        foreach ($this->erpContracts as $erpContract) {
            $erpContractsArray[$erpContract['id']] = [SharedTaskData::SHAREABLE_TYPE => SharedTaskData::CONTRACT];
        }

        $sharedTaskData->erpContracts()->sync($erpContractsArray);
        $sharedTaskData->erpEmployees()->sync($erpEmployeesArray);

        return $sharedTaskData;
    }
}
