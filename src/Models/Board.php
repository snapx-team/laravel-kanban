<?php

namespace Xguard\LaravelKanban\Models;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Xguard\LaravelKanban\Models\Log;

class Board extends Model
{
    use SoftDeletes, CascadeSoftDeletes;
    
    protected $dates = ['deleted_at'];

    protected $cascadeDeletes = ['members', 'rows', 'templates'];

    protected $table = 'kanban_boards';

    protected $guarded = [];
    
    public function logs()
    {
        return $this->morphMany(Log::class, 'loggable');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(Row::class)->orderBy('index');
    }

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(Template::class, 'kanban_board_template', 'board_id', 'template_id');
    }
}
