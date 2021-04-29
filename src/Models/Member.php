<?php

namespace Xguard\LaravelKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $table = 'kanban_members';

    protected $guarded = [];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function phoneLine(): BelongsTo
    {
        return $this->belongsTo(PhoneLine::class);
    }

    public function employeeCards(): HasMany
    {
        return $this->HasMany(EmployeeCard::class);
    }
}
