<?php

namespace Xguard\LaravelKanban\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Column extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'kanban_columns';

    protected $cascadeDeletes = ['taskCards'];

    protected $guarded = [];

    public function logs()
    {
        return $this->morphMany(Log::class, 'loggable');
    }

    public function row(): BelongsTo
    {
        return $this->belongsTo(Row::class)->orderBy('index', 'asc');
    }

    public function taskCards(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('index', 'asc');
    }
}
