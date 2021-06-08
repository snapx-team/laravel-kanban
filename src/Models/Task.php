<?php

namespace Xguard\LaravelKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;



class Task extends Model
{
    protected $table = 'kanban_tasks';

    protected $guarded = [];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function badge(): HasOne
    {
        return $this->hasOne(Badge::class);
    }

    public function assignedTo(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'kanban_employee-task', 'task_id', 'employee_id');
    }

}
