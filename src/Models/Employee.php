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

class Employee extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'kanban_employees';
    protected $cascadeDeletes = ['members','employeeBoardNotificationSetting'];
    protected $guarded = [];

    const ID = 'id';
    const USER_ID = 'user_id';
    const LAST_NOTIF_CHECK = 'last_notif_check';
    const USER_RELATION_NAME = 'user';
    const NOTFICIATIONS_RELATION_NAME = 'notifications';

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
        return $this->belongsTo(Employee::class, self::ID);
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
        return $this->morphMany(Log::class, Log::LOGGABLE_RELATION_NAME);
    }

    public function employeeBoardNotificationSetting(): hasMany
    {
        return $this->hasMany(EmployeeBoardNotificationSetting::class);
    }
}
