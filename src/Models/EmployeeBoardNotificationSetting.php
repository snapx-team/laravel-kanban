<?php

namespace Xguard\LaravelKanban\Models;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string|null $options
 */

class EmployeeBoardNotificationSetting extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $table = 'kanban_employee_board_notification_setting';

    protected $cascadeDeletes = ['boards', 'employees'];

    protected $guarded = [];

    protected $appends = [
        'unserialized_options',
    ];
    public function getUnserializedOptionsAttribute()
    {
        return unserialize($this->ignore_types);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }
}
