<?php

namespace Xguard\LaravelKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Column extends Model
{
    protected $table = 'kanban_columns';

    protected $guarded = [];

    public function row(): BelongsTo
    {
        return $this->belongsTo(Row::class);
    }

    public function taskCards(): HasMany
    {
        return $this->hasMany(TaskCard::class)->orderBy('index', 'asc');
    }
}
