<?php

namespace Xguard\LaravelKanban\Models;

use App\Models\User;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Xguard\LaravelKanban\Models\Log;

class Employee extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'kanban_employees';

    protected $cascadeDeletes = ['members', 'tasks', 'logs'];

    protected $guarded = [];


    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault(function ($user) {
            $user->first_name = 'DELETED';
            $user->last_name = 'USER';
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'id');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'kanban_employee_task', 'employee_id', 'task_id');
    }

    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Log::class, 'kanban_employee_log', 'employee_id', 'log_id')->withTimestamps();
    }

    public function logs(): MorphMany
    {
        return $this->morphMany(Log::class, 'loggable');
    }

    public function employeeBoardNotificationSetting(): hasMany
    {
        return $this->hasMany(EmployeeBoardNotificationSetting::class);
    }
}
