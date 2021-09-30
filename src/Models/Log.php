<?php

namespace Xguard\LaravelKanban\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Xguard\LaravelKanban\Models\Task;
use Illuminate\Support\Facades\Auth;

class Log extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'kanban_logs';

    protected $guarded = [];

    const TYPE_EMPLOYEE_CREATED = 1;
    const TYPE_EMPLOYEE_UPDATED = 2;
    const TYPE_EMPLOYEE_DELETED = 3;

    const TYPE_CARD_CREATED = 10;
    const TYPE_CARD_CANCELED = 11;
    const TYPE_CARD_COMPLETED = 12;
    const TYPE_CARD_MOVED = 14;
    const TYPE_CARD_ASSIGNED_TO_BOARD = 15;
    const TYPE_CARD_UPDATED = 16;
    const TYPE_CARD_ASSIGNED_GROUP = 17;
    const TYPE_CARD_CHANGED_INDEX = 18; //this is never used
    const TYPE_CARD_CHECKLIST_ITEM_CHECKED = 20;
    const TYPE_CARD_CHECKLIST_ITEM_UNCHECKED = 21;
    const TYPE_CARD_ASSIGNED_TO_USER = 22;
    const TYPE_CARD_UNASSIGNED_TO_USER = 23;

    const TYPE_TEMPLATE_CREATED = 30;
    const TYPE_TEMPLATE_UPDATED = 31;
    const TYPE_TEMPLATE_DELETED = 32;


    const TYPE_KANBAN_MEMBER_CREATED = 40;
    const TYPE_KANBAN_MEMBER_DELETED = 41;

    const TYPE_BOARD_CREATED = 60;
    const TYPE_BOARD_DELETED = 61;
    const TYPE_BOARD_EDITED = 62;

    const TYPE_COMMENT_CREATED = 70;
    const TYPE_COMMENT_DELETED = 71;
    const TYPE_COMMENT_EDITED = 72;

    const TYPE_ROW_CREATED = 80;
    const TYPE_ROW_DELETED = 81;
    const TYPE_ROW_UPDATED = 82;
    const TYPE_COLUMN_CREATED = 83;
    const TYPE_COLUMN_DELETED = 84;
    const TYPE_COLUMN_UPDATED = 85;

    const TYPE_BADGE_CREATED = 90;

    const TYPE_KANBAN_COLUMNS_CREATED_OR_UPDATED = 100;

    public static function createLog(
        ?int $userId,
        int $logId,
        string $description = '',
        ?int $targetedEmployeeId,
        int $logabbleId,
        string $loggableType
    ) {
        $employeeArray = [];

        if ($loggableType == 'Xguard\LaravelKanban\Models\Task') {
            $task = Task::find($logabbleId);

            //notify reporter
            if (session('employee_id') !== $task->reporter_id) {
                $employee = Employee::find($task->reporter_id);
                array_push($employeeArray, $employee->id);
            }
            //notify assigned to users
            foreach ($task->assignedTo as $employee) {
                array_push($employeeArray, $employee['employee']['id']);
            }

            if ($targetedEmployeeId !== null) {
                //do it if there is a targeted emplployee id
                array_push($employeeArray, $targetedEmployeeId);
            }
        }

        $log = Log::create([
            'user_id' => $userId,
            'log_type' => $logId,
            'description' => $description,
            'targeted_employee_id' =>  $targetedEmployeeId,
            'loggable_id' => $logabbleId,
            'loggable_type' => $loggableType,
        ]);

        $log->notifAssignedTo()->sync($employeeArray);

        return $log;
    }

    public function loggable()
    {
        return $this->morphTo();
    }

    public function targeted_employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'targeted_employee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifAssignedTo(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'kanban_employee_log', 'log_id', 'employee_id')->withTimestamps();
    }
}
