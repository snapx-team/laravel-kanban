<?php

namespace Xguard\LaravelKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;

/**
 * @property \Illuminate\Support\Carbon|null $deadline
 * @property int $id
 * @property Board $board
 * @property string $name
 */

class TaskVersion extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];
    
    protected $dates = ['deleted_at'];

    protected $table = 'kanban_tasks_versions';

    public function log(): BelongsTo
    {
        return $this->belongsTo(Log::class, 'log_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
