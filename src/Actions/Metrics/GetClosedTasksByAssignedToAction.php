<?php

namespace Xguard\LaravelKanban\Actions\Metrics;

use DateTime;
use Exception;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Log;

class GetClosedTasksByAssignedToAction extends Action
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
        $logs = Log::where('log_type', Log::TYPE_CARD_COMPLETED)
            ->whereDate('created_at', '>=', new DateTime($this->start))
            ->whereDate('created_at', '<=', new DateTime($this->end))
            ->where('loggable_type', 'Xguard\LaravelKanban\Models\Task')
            ->orderBy('loggable_id')
            ->latest()
            ->get()
            ->unique('loggable_id');

        $names = [];
        $hits = [];
        foreach ($logs as $log) {
            foreach ($log->loggable->assignedTo as $employee) {
                // check if original task status is still completed
                if ($log->loggable->status === 'completed') {
                    if (array_key_exists($employee['employee']['user']['id'], $hits)) {
                        $hits[$employee['employee']['user']['id']] += 1;
                    } else {
                        $hits[$employee['employee']['user']['id']] = 1;
                        array_push($names, $employee['employee']['user']['full_name']);
                    }
                }
            }
        }

        return [
            'hits' => array_values($hits),
            'names' => $names,
        ];
    }
}
