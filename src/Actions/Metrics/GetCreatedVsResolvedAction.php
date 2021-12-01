<?php

namespace Xguard\LaravelKanban\Actions\Metrics;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Log;

class GetCreatedVsResolvedAction extends Action
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
        $beginning = new DateTime($this->start);
        $ending = new DateTime($this->end);
        $resolvedLogs = Log::where('log_type', Log::TYPE_CARD_COMPLETED)
            ->orWhere('log_type', Log::TYPE_CARD_CANCELLED)
            ->whereDate('created_at', '>=', $beginning)
            ->whereDate('created_at', '<=', $ending)
            ->where('loggable_type', 'Xguard\LaravelKanban\Models\Task')
            ->orderBy('loggable_id')
            ->get();
        $createdLogs = Log::with('user')
            ->whereDate('created_at', '>=', $beginning)
            ->whereDate('created_at', '<=', $ending)
            ->where('log_type', Log::TYPE_CARD_CREATED)
            ->where('loggable_type', 'Xguard\LaravelKanban\Models\Task')
            ->orderBy('loggable_id')
            ->get();

        $period = new DatePeriod($beginning, new DateInterval('P1D'), $ending);

        $series = [
            (object)[
                'name' => 'Created',
                'data' => []
            ],
            (object)[
                'name' => 'Resolved',
                'data' => []
            ]
        ];

        $categories = [];
        foreach ($period as $day) {
            $currentDay = $day->format("m/d");
            array_push($categories, $currentDay);
            $count = 0;
            foreach ($createdLogs as $createdLog) {
                $tempDay = new DateTime($createdLog->created_at);
                $createdDay = $tempDay->format("m/d");
                if ($currentDay == $createdDay) {
                    $count += 1;
                }
            }
            array_push($series[0]->data, $count);

            $count = 0;
            foreach ($resolvedLogs as $resolvedLog) {
                $tempDay = new DateTime($resolvedLog->created_at);
                $resolvedDay = $tempDay->format("m/d");
                if ($currentDay == $resolvedDay) {
                    $count += 1;
                }
            }
            array_push($series[1]->data, $count);
        }

        return [
            'series' => $series,
            'categories' => $categories,
        ];
    }
}
