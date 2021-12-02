<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Xguard\LaravelKanban\Models\Task;

class TaskFile extends Model
{
    protected $guarded = [];

    protected $table = "kanban_task_files";
    
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
