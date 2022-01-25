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

class GetAverageTimeToCompletionByEmployeeAction extends Action
{
    const START = 'start';
    const END = 'end';
    const START_REQUIRED = 'start.required';
    const START_DATE = 'start.date';
    const START_BEFORE_OR_EQUAL = 'start.before_or_equal';
    const END_REQUIRED = 'end.required';
    const END_DATE = 'end.date';
    const END_AFTER_OR_EQUAL = 'end.after_or_equal';
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
            self::START => ['required', 'date', 'before_or_equal:today'],
            self::END => ['required', 'date', 'after_or_equal:start']
        ];
    }

    public function messages(): array
    {
        return [
            self::START_REQUIRED => 'Start date is required',
            self::START_DATE => 'Start date needs to be in a date format',
            self::START_BEFORE_OR_EQUAL => 'Start date cannot be in the future',
            self::END_REQUIRED => 'End date is required',
            self::END_DATE => 'End date needs to be in a date format',
            self::END_AFTER_OR_EQUAL => 'End date needs to be after start date'
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

        $assignedLogs = Log::with([
            Log::LOGGABLE_RELATION_NAME => function (MorphTo $morphTo) {
                $morphTo->withoutGlobalScope(SoftDeletingScope::class)
                    ->morphWith([Task::class => [Task::ASSIGNED_TO_RELATION_NAME]]);
            }
        ])->where(Log::LOG_TYPE, Log::TYPE_CARD_ASSIGNED_TO_USER)
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
            self::NAMES => $names,
            self::HITS => $averages,
        ];
    }
}
