<?php

namespace Xguard\LaravelKanban\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Xguard\LaravelKanban\QueryBuilders\TasksQueryBuilder;

class TaskVersion extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $dates = ['deleted_at'];
    protected $table = 'kanban_tasks_versions';
    protected $appends = [
        'previous_task_version',
    ];

    const BOARD_RELATION_NAME = 'board';


    public function newEloquentBuilder($query): TasksQueryBuilder
    {
        return new TasksQueryBuilder($query);
    }

    public function log(): BelongsTo
    {
        return $this->belongsTo(Log::class, 'log_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function allTaskVersions(): HasMany
    {
        return $this->hasMany(TaskVersion::class, 'task_id', 'task_id');
    }

    public function getPreviousTaskVersionAttribute()
    {
        $taskVersion = TaskVersion::where('task_id', $this->task_id)->orderBy('id', 'desc')->where('id', '<', $this->id)->withTaskData()->first();

        // We remove the previous_task_version attribute to avoid deeply nested taskVersion objects through recursion
        if ($taskVersion) {
            $taskVersion->setAppends([]);
        }
        return $taskVersion;
    }

    public function sharedTaskData(): BelongsTo
    {
        return $this->belongsTo(SharedTaskData::class, 'shared_task_data_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reporter_id')->withDefault(function ($reporter) {
            $reporter->user->first_name = 'DELETED';
            $reporter->user->last_name = 'USER';
        });
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class, 'badge_id')->withTrashed();
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'board_id')->withTrashed();
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class)->withTrashed();
    }

    public function row(): BelongsTo
    {
        return $this->belongsTo(Row::class)->withTrashed();
    }

    public function assignedTo(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'kanban_employee_task', 'task_id', 'employee_id');
    }

    public function getDeadlineAttribute($value): string
    {
        return Carbon::parse($value)->format('c');
    }

    public function taskFiles(): HasMany
    {
        return $this->hasMany(TaskFile::class, 'task_id', 'task_id');
    }

    public function comments(): HasMany
    {
        return $this->HasMany(Comment::class, 'task_id', 'task_id');
    }
}
