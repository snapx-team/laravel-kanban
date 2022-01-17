<?php

namespace Xguard\LaravelKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'kanban_comments';
    protected $guarded = [];

    const ID = 'id';

    const TASK_RELATION_NAME = 'task';
    const EMPLOYEE_RELATION_NAME = 'employee';
    const LOGS_RELATION_NAME = 'logs';

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function logs()
    {
        return $this->morphMany(Log::class, 'loggable');
    }
}
