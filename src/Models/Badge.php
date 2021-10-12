<?php

namespace Xguard\LaravelKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Xguard\LaravelKanban\Models\Log;

class Badge extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'kanban_badges';

    protected $guarded = [];

    public function logs()
    {
        return $this->morphMany(Log::class, 'loggable');
    }

    public function taskCard(): HasOne
    {
        return $this->hasOne(Task::class);
    }

    public function template(): HasOne
    {
        return $this->hasOne(Template::class);
    }
}
