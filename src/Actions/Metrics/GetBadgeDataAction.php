<?php

namespace Xguard\LaravelKanban\Actions\Metrics;

use DateTime;
use DB;
use Exception;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Task;

class GetBadgeDataAction extends Action
{
    public function authorize()
    {
        return session('role') === 'admin';
    }

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
            'end.after_or_equal' => 'End date needs to be after start date'
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

        $tasks = Task::select('badge_id', DB::raw('count(*) as total'))
            ->groupBy('badge_id')
            ->whereDate('created_at', '>=', new DateTime($this->start))
            ->whereDate('created_at', '<=', new DateTime($this->end))
            ->get();

        $badgeData = [];
        foreach ($tasks as $task) {
             $badgeData[$task->badge->name] = $task->total;
        }

        return [
            'names' => array_keys($badgeData),
            'hits' => array_values($badgeData),
        ];
    }
}
