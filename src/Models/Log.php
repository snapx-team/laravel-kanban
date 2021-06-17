<?php

namespace Xguard\LaravelKanban\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    protected $table = 'kanban_logs';

    protected $guarded = [];

    const TYPE_EMPLOYEE_CREATED = 1;
    const TYPE_EMPLOYEE_UPDATED = 2;
    const TYPE_EMPLOYEE_DELETED = 3;

    const TYPE_PHONE_LINE_CREATED = 4;
    const TYPE_PHONE_LINE_UPDATED = 5;
    const TYPE_PHONE_LINE_DELETED = 6;

    const TYPE_EMPLOYEE_CARD_CREATED = 7;
    const TYPE_EMPLOYEE_CARD_DELETED = 8;
    const TYPE_EMPLOYEE_CARD_CLOSED = 22;
    const TYPE_EMPLOYEE_CARD_ASSIGNED = 23;

    const TYPE_KANBAN_COLUMNS_CREATED_OR_UPDATED = 9;

    const TYPE_KANBAN_MEMBER_CREATED = 10;
    const TYPE_KANBAN_MEMBER_DELETED = 11;

//    public static function createLog(?int $userId, int $logId, string $description = '')
//    {
//        return Log::create([
//            'user_id' => $userId,
//            'log_type' => $logId,
//            'description' => $description
//        ]);
//    }



    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
