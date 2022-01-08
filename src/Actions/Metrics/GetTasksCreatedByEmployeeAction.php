<?php

namespace Xguard\LaravelKanban\Actions\Metrics;

use DateTime;
use DB;
use Exception;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Task;

class GetTasksCreatedByEmployeeAction extends Action
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
     *
     * @return array
     * @throws Exception
     */
    public function handle(): array
    {
        $tasks = Task::select('reporter_id', DB::raw('count(*) as total'))
            ->groupBy('reporter_id')
            ->whereDate('created_at', '>=', new DateTime($this->start))
            ->whereDate('created_at', '<=', new DateTime($this->end))
            ->get();

        $reportersData = [];
        if (count($tasks) > 0) {
            foreach ($tasks as $task) {
                // needed to account for deleted users where multiple names can be "DELETED USER"
                if (array_key_exists($task->reporter->user->full_name, $reportersData)) {
                    $reportersData[$task->reporter->user->full_name] += $task->total;
                } else {
                    $reportersData[$task->reporter->user->full_name] = $task->total;
                }
            }
        }

        return [
            'hits' => array_values($reportersData),
            'names' => array_keys($reportersData),
        ];
    }
}
