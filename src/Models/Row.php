<?php

namespace Xguard\LaravelKanban\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Xguard\LaravelKanban\Models\Log;

class Row extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'kanban_rows';
    protected $cascadeDeletes = ['columns'];
    protected $guarded = [];

    const COLUMNS_RELATION_NAME = 'columns';
    const BOARD_RELATION_NAME = 'board';
    const LOGS_RELATION_NAME = 'logs';

    public function logs()
    {
        return $this->morphMany(Log::class, 'loggable');
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function columns(): HasMany
    {
        return $this->HasMany(Column::class)->orderBy('index');
    }

}
