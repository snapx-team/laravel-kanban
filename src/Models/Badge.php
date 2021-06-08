<?php

namespace Xguard\LaravelKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Badge extends Model
{
    protected $table = 'kanban_badges';

    protected $guarded = [];

    public function taskCard(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
