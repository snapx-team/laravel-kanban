<?php

namespace Xguard\LaravelKanban\Actions\Metrics;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Enums\LoggableTypes;
use Xguard\LaravelKanban\Enums\Roles;
use Xguard\LaravelKanban\Enums\SessionVariables;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

class GetAverageTimeToCompletionByBadgeAction extends Action
{
    const NAMES = 'names';
    const HITS = 'hits';

    public function authorize()
    {
        return session(SessionVariables::ROLE()->getValue()) === Roles::ADMIN()->getValue();
    }

    /**
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
            'end.after_or_equal' => 'End date needs to be after start date'
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function handle(): array
    {
        $closedLogs = Log::where(Log::LOG_TYPE, Log::TYPE_CARD_COMPLETED)
            ->whereDate(Log::CREATED_AT, '>=', new DateTime($this->start))
            ->whereDate(Log::CREATED_AT, '<=', new DateTime($this->end))
            ->where(Log::LOGGABLE_TYPE, LoggableTypes::TASK()->getValue())
            ->orderBy(Log::LOGGABLE_ID)->get();

        $assignedLogs = Log::with([Log::LOGGABLE_RELATION_NAME => function (MorphTo $morphTo) {
            $morphTo->withoutGlobalScope(SoftDeletingScope::class);
        }])->where(Log::LOG_TYPE, Log::TYPE_CARD_ASSIGNED_TO_USER)
            ->whereDate(Log::CREATED_AT, '>=', new DateTime($this->start))
            ->whereDate(Log::CREATED_AT, '<=', new DateTime($this->end))
            ->where(Log::LOGGABLE_TYPE, LoggableTypes::TASK()->getValue())
            ->orderBy(Log::LOGGABLE_ID)
            ->latest()
            ->get()
            ->unique(Log::LOGGABLE_ID);

        $names = [];
        $hits = [];
        foreach ($assignedLogs as $assignedLog) {
            foreach ($closedLogs as $key => $closedLog) {
                if ($assignedLog->loggable_id == $closedLog->loggable_id) {
                    $beginning = new DateTime($assignedLog->created_at);
                    $end = new DateTime($closedLog->created_at);
                    $hours = ($end->diff($beginning))->h;
                    if (array_key_exists($assignedLog->loggable->badge_id, $hits)) {
                        array_push($hits[$assignedLog->loggable->badge_id], $hours);
                        unset($closedLogs[$key]);
                    } else {
                        $hits[$assignedLog->loggable->badge_id] = [$hours];
                        array_push($names, $assignedLog->loggable->badge->name);
                    }
                }
            }
        }

        $averages = [];
        foreach ($hits as $value) {
            array_push($averages, sprintf("%.2f", array_sum($value) / count($value)));
        }

        return [
            self::NAMES => $names,
            self::HITS => $averages,
        ];
    }
}
