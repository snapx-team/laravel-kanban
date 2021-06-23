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

    const TYPE_CARD_CREATED = 10;
    const TYPE_CARD_DELETED = 11;
    const TYPE_CARD_CLOSED = 12;
    const TYPE_CARD_ASSIGNED = 13;

    const TYPE_KANBAN_COLUMNS_CREATED_OR_UPDATED = 15;

    const TYPE_KANBAN_MEMBER_CREATED = 20;
    const TYPE_KANBAN_MEMBER_DELETED = 21;

    const TYPE_BOARD_CREATED = 60;
    const TYPE_BOARD_DELETED = 61;

    const TYPE_COMMENT_CREATED = 70;
    const TYPE_COMMENT_DELETED = 71;

   public static function createLog(?int $userId, int $logId, string $description = '',
        ?int $badgeId, ?int $boardId, ?int $taskId, ?int $erpEmployeeId, ?int $erpJobSiteId, ?string $role)
   {
       return Log::create([
        'user_id' => $userId,
        'log_type' => $logId,
        'description' => $description,
        'badge_id' => $badgeId,
        'board_id' => $boardId,
        'task_id' => $taskId,
        'erp_employee_id' => $erpEmployeeId,
        'erp_job_site_id' => $erpJobSiteId,
        'role' => $role
    ]);
   }



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
