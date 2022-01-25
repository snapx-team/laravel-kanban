<?php

namespace Xguard\LaravelKanban\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Row extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'kanban_rows';
    protected $cascadeDeletes = ['columns'];
    protected $guarded = [];

    const ID = 'id';
    const BOARD_ID = 'board_id';
    const INDEX = 'index';

    const COLUMNS_RELATION_NAME = 'columns';
    const BOARD_RELATION_NAME = 'board';
    const LOGS_RELATION_NAME = 'logs';

    public function logs()
    {
        return $this->morphMany(Log::class, Log::LOGGABLE_RELATION_NAME);
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function columns(): HasMany
    {
        return $this->HasMany(Column::class)->orderBy(Column::INDEX);
    }
}
