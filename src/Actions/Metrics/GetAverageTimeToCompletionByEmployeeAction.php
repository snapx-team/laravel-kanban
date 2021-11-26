<?php

namespace Xguard\LaravelKanban\Actions\Metrics;

use DateTime;
use Exception;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Log;

class GetAverageTimeToCompletionByEmployeeAction extends Action
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
        $closedLogs = Log::where('log_type', Log::TYPE_CARD_COMPLETED)
            ->whereDate('created_at', '>=', new DateTime($this->start))
            ->whereDate('created_at', '<=', new DateTime($this->end))
            ->where('loggable_type', 'Xguard\LaravelKanban\Models\Task')
            ->orderBy('loggable_id')
            ->get();
        $assignedLogs = Log::whereDate('created_at', '>=', new DateTime($this->start))
            ->whereDate('created_at', '<=', new DateTime($this->end))
            ->where('log_type', Log::TYPE_CARD_ASSIGNED_TO_USER)
            ->where('loggable_type', 'Xguard\LaravelKanban\Models\Task')
            ->orderBy('loggable_id')
            ->latest()
            ->get()
            ->unique('loggable_id');

        $names = [];
        $hits = [];
        foreach ($assignedLogs as $assignedLog) {
            foreach ($closedLogs as $key => $closedLog) {
                if ($assignedLog->loggable_id == $closedLog->loggable_id) {
                    $beginning = new DateTime($assignedLog->created_at);
                    $end = new DateTime($closedLog->created_at);
                    $diff = $end->diff($beginning);
                    $hours = $diff->h;
                    $hours = $hours + ($diff->days * 24);

                    foreach ($assignedLog->loggable->assignedTo as $user) {
                        if (array_key_exists($user->employee->user->id, $hits)) {
                            array_push($hits[$user->employee->user->id], $hours);
                            unset($closedLogs[$key]);
                        } else {
                            $hits[$user->employee->user->id] = [$hours];
                            array_push($names, $user->employee->user->full_name);
                        }
                    }
                }
            }
        }

        $averages = [];
        foreach ($hits as $value) {
            array_push($averages, sprintf("%.2f", array_sum($value) / count($value)));
        }

        return [
            'names' => $names,
            'hits' => $averages,
        ];
    }
}
