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

    const ROW_ID = 'row_id';
    const INDEX = 'index';

    const LOGS_RELATION_NAME = 'logs';
    const ROW_RELATION_NAME = 'row';
    const TASK_CARDS_RELATION_NAME = 'taskCards';

    public function logs()
    {
        return $this->morphMany(Log::class, Log::LOGGABLE_RELATION_NAME);
    }

    public function row(): BelongsTo
    {
        return $this->belongsTo(Row::class)->orderBy(Row::INDEX);
    }

    public function taskCards(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy(Task::INDEX);
    }
}
