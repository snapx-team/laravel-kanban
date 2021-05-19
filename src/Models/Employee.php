<?php

namespace Xguard\LaravelKanban\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $table = 'kanban_employees';

    protected $guarded = [];

    public function taskCards(): HasMany
    {
        return $this->hasMany(TaskCard::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
