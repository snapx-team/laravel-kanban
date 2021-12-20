<?php

namespace Xguard\LaravelKanban\Actions\Metrics;

use DateTime;
use Exception;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Task;

class GetContractDataAction extends Action
{
    public function authorize()
    {
        return session('role') === 'admin';
    }
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'start' => ['required', 'date','before_or_equal:today'],
            'end' => ['required','date','after_or_equal:start']
        ];
    }

    public function messages(): array
    {
        return [
            'start.required' => 'Start date is required',
            'start.date' => 'Start date needs to be in a date format',
            'start.before_or_equal' => 'Start date cannot be in the future',
            'end.required' => 'End date is required',
            'end.date' => 'End date needs to be in a date format',
            'end.after' => 'End date needs to be after start date'
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return array
     * @throws Exception
     */
    public function handle(): array
    {
        $tasks = Task::whereDate('created_at', '>=', new DateTime($this->start))
            ->whereDate('created_at', '<=', new DateTime($this->end))
            ->get();

        $contractData = [];
        foreach ($tasks as $task) {
            foreach ($task->sharedTaskData->erpContracts as $contract) {
                if (array_key_exists($contract->contract_identifier, $contractData)) {
                    $contractData[$contract->contract_identifier] += 1;
                } else {
                    $contractData[$contract->contract_identifier] = 1;
                }
            }
        }

        return [
            'hits' => array_values($contractData),
            'names' => array_keys($contractData),
        ];
    }
}
