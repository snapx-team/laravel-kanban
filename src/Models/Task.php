<?php

namespace Xguard\LaravelKanban\Models;

use App\Models\User;
use App\Models\JobSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

/**
 * @property \Illuminate\Support\Carbon|null $deadline
 */

class Task extends Model
{
    protected $table = 'kanban_tasks';

    protected $guarded = [];

    protected $appends = [
        'hours_to_deadline',
    ];

    public function sharedTaskData(): BelongsTo
    {
        return $this->belongsTo(SharedTaskData::class, 'shared_task_data_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
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

    public function erpJobSite(): BelongsTo
    {
        return $this->belongsTo(JobSite::class, 'erp_job_site_id');
    }

    public function assignedTo(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'kanban_employee_task', 'task_id', 'employee_id');
    }

    public function comments(): HasMany
    {
        return $this->HasMany(Comment::class);
    }

    public function logs(): HasMany
    {
        return $this->HasMany(Log::class);
    }

    public function getHoursToDeadlineAttribute()
    {
        if ($this->deadline === null) {
            return null;
        } else {
            $now = Carbon::now();
            $deadline = new Carbon($this->deadline);
            return $now->diffInHours($deadline, false);
        }
    }
}
