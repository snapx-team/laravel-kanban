<?php

namespace Xguard\LaravelKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Badge extends Model
{
    protected $table = 'kanban_badges';

    protected $guarded = [];

    public function taskCard(): HasOne
    {
        return $this->hasOne(Task::class);
    }

    public function template(): HasOne
    {
        return $this->hasOne(Template::class);
    }
}
