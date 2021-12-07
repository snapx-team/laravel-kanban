<?php

namespace Xguard\LaravelKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Xguard\LaravelKanban\AWSStorage\S3Storage;

class TaskFile extends Model
{
    protected $guarded = [];

    protected $appends = [
        'full_url',
    ];

    protected $table = "kanban_task_files";

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function getFullUrlAttribute(): string
    {
        $disk = app(S3Storage::class);
        return $disk->url($this->task_file_url);
    }
}
