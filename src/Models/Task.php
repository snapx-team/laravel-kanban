<?php

namespace Xguard\LaravelKanban\Models;

use App\Models\User;
use App\Models\Contract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Xguard\LaravelKanban\Models\Log;
use Carbon\Carbon;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        return $this->belongsTo(Employee::class, 'reporter_id');
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class, 'badge_id');
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'board_id');
    }

    public function erpEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'erp_employee_id');
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function row(): BelongsTo
    {
        return $this->belongsTo(Row::class);
    }

    public function erpContract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'erp_contract_id');
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
        return $this->morphMany(Log::class, 'loggable');
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
        return strtoupper(substr($this->board->name, 0, 3)) . '-' . $this->id;
    }

    public function getDeadlineAttribute($value): string
    {
        return Carbon::parse($value)->format('c');
    }
}
