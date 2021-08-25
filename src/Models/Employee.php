<?php

namespace Xguard\LaravelKanban\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Employee extends Model
{
    protected $table = 'kanban_employees';

    protected $guarded = [];


    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'id');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'kanban_employee_task', 'employee_id', 'task_id');
    }

    public function logs(): BelongsToMany
    {
        return $this->belongsToMany(Log::class, 'kanban_employee_log', 'employee_id', 'log_id')->withTimestamps();
    }
}
