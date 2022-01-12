<?php

namespace Xguard\LaravelKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'kanban_members';
    protected $guarded = [];

    const EMPLOYEE_ID = 'employee_id';
    const EMPLOYEE_RELATION_NAME = 'employee';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }
}
