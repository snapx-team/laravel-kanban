<?php

namespace Xguard\LaravelKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Xguard\LaravelKanban\Enums\DateTimeFormats;
use Xguard\LaravelKanban\QueryBuilders\TasksQueryBuilder;

/**
 * @property \Illuminate\Support\Carbon|null $deadline
 * @property int $id
 * @property Board $board
 * @property string $name
 */

class Task extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'kanban_tasks';
    protected $cascadeDeletes = ['comments', 'assignedTo'];
    protected $guarded = [];
    protected $appends = [
        'hours_to_deadline',
        'task_simple_name'
    ];

    const ID = 'id';
    const INDEX = 'index';
    const STATUS = 'status';
    const BADGE_ID = 'badge_id';
    const BOARD_ID = 'board_id';
    const DEADLINE = 'deadline';
    const REPORTER_ID = 'reporter_id';
    const SHARED_TASK_DATA_RELATION_ID='shared_task_data_id';
    const CREATED_AT = 'created_at';
    const DELETED_AT = 'deleted_at';

    const ROW_RELATION_NAME = 'row';
    const COLUMN_RELATION_NAME = 'column';
    const TASK_FILES_RELATION_NAME = 'taskFiles';
    const SHARED_TASK_DATA_RELATION_NAME='sharedTaskData';
    const ASSIGNED_TO_RELATION_NAME ='assignedTo';
    const REPORTER_RELATION_NAME ='reporter';
    const BADGE_RELATION_NAME ='badge';
    const BOARD_RELATION_NAME = 'board';
    const TASK_VERSION_RELATION_NAME = 'taskVersion';

    public function newEloquentBuilder($query): TasksQueryBuilder
    {
        return new TasksQueryBuilder($query);
    }

    public function sharedTaskData(): BelongsTo
    {
        return $this->belongsTo(SharedTaskData::class, 'shared_task_data_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Employee::class, self::REPORTER_ID)->withDefault(function ($reporter) {
            $reporter->user->first_name = 'DELETED';
            $reporter->user->last_name = 'USER';
        });
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class, self::BADGE_ID);
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, self::BOARD_ID);
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function row(): BelongsTo
    {
        return $this->belongsTo(Row::class);
    }

    public function assignedTo(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'kanban_employee_task', 'task_id', 'employee_id');
    }

    public function comments(): HasMany
    {
        return $this->HasMany(Comment::class);
    }

    public function logs()
    {
        return $this->morphMany(Log::class, Log::LOGGABLE_RELATION_NAME);
    }

    public function taskFiles(): HasMany
    {
        return $this->hasMany(TaskFile::class);
    }

    public function getHoursToDeadlineAttribute(): ?int
    {
        if ($this->deadline === null) {
            return null;
        } else {
            $now = Carbon::now();
            $deadline = new Carbon($this->deadline);
            return $now->diffInHours($deadline, false);
        }
    }

    public function getTaskSimpleNameAttribute(): string
    {
        $boardName = $this->board()->withTrashed()->first()->name;
        return strtoupper(substr($boardName, 0, 3)) . '-' . $this->id;
    }

    public function getDeadlineAttribute($value): string
    {
        return Carbon::parse($value)->format(DateTimeFormats::PARSE_TO_ISO8601()->getValue());
    }

    public function taskVersion(): HasOne
    {
        return $this->hasOne(TaskVersion::class);
    }
}
