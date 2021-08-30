<?php

namespace Xguard\LaravelKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SharedTaskData extends Model
{
    protected $table = 'kanban_shared_task_data';

    protected $guarded = [];

    public function taskCards(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('name', 'asc');
    }
}
