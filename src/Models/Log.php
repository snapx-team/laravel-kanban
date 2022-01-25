<?php

namespace Xguard\LaravelKanban\Models;

use App\Models\User;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Xguard\LaravelKanban\Actions\Notifications\NotifyEmployeesAction;
use Xguard\LaravelKanban\Repositories\LogsRepository;

/**
 * @property int $task_id
 * @property int $id
 */
class Log extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    const TABLE_NAME = 'kanban_logs';

    //LOG GROUP NAMES
    const EMPLOYEE_GROUP = 'EMPLOYEE_GROUP';
    const TASK_STATUS_GROUP = 'TASK_STATUS_GROUP';
    const TASK_MOVEMENT_GROUP = 'TASK_MOVEMENT_GROUP';
    const TASK_INFORMATION_UPDATE_GROUP = 'TASK_INFORMATION_UPDATE_GROUP';
    const TASK_CHECKLIST_GROUP = 'TASK_CHECKLIST_GROUP';
    const TASK_ASSIGNATION_TO_USER_GROUP = 'TASK_ASSIGNATION_TO_USER_GROUP';
    const BOARD_MEMBER_GROUP = 'BOARD_MEMBER_GROUP';
    const COMMENT_GROUP = 'COMMENT_GROUP';
    const MENTIONS_GROUP = 'MENTIONS_GROUP';

    //LOG FIELDS
    const ID = 'id';
    const LOG_TYPE = 'log_type';
    const LOGGABLE_TYPE = 'loggable_type';
    const LOGGABLE_ID = 'loggable_id';
    const USER_ID = 'user_id';
    const DESCRIPTION = 'description';
    const TARGETED_EMPLOYEE_ID = 'targeted_employee_id';
    const DELETED_AT = 'deleted_at';

    //LOG RELATION NAMES
    const LOGGABLE_RELATION_NAME = 'loggable';
    const TASK_VERSION_RELATION_NAME = 'taskVersion';
    const TARGET_EMPLOYEE_RELATION_NAME = 'targetEmployee';
    const USER_RELATION_NAME = 'user';
    const NOTIFICATION_EMPLOYEE_RELATION_NAME = 'notifications';

    //PIVOT TABLE NAMES
    const KANBAN_EMPLOYEE_LOG_TABLE_NAME = 'kanban_employee_log';

    //LOG TYPES
    const TYPE_EMPLOYEE_CREATED = 1;
    const TYPE_EMPLOYEE_UPDATED = 2;
    const TYPE_EMPLOYEE_DELETED = 3;

    const TYPE_CARD_CREATED = 10;
    const TYPE_CARD_CANCELLED = 11;
    const TYPE_CARD_COMPLETED = 12;
    const TYPE_CARD_ACTIVATED = 13;
    const TYPE_CARD_MOVED = 14;
    const TYPE_CARD_PLACED = 15;
    const TYPE_CARD_UPDATED = 16;
    const TYPE_CARD_ASSIGNED_GROUP = 17;
    const TYPE_CARD_CHANGED_INDEX = 18;
    const TYPE_CARD_CHECKLIST_ITEM_CHECKED = 20;
    const TYPE_CARD_CHECKLIST_ITEM_UNCHECKED = 21;
    const TYPE_CARD_ASSIGNED_TO_USER = 22;
    const TYPE_CARD_UNASSIGNED_TO_USER = 23;
    const TYPE_CARD_REMOVED_FROM_GROUP = 24;
    const TYPE_CARD_FILE_ADDED = 25;
    const TYPE_CARD_FILE_REMOVED= 26;

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
    const TYPE_COMMENT_MENTION_CREATED = 73;

    const TYPE_ROW_CREATED = 80;
    const TYPE_ROW_DELETED = 81;
    const TYPE_ROW_UPDATED = 82;
    const TYPE_COLUMN_CREATED = 83;
    const TYPE_COLUMN_DELETED = 84;
    const TYPE_COLUMN_UPDATED = 85;

    const TYPE_BADGE_CREATED = 90;
    const TYPE_BADGE_EDITED = 91;
    const TYPE_BADGE_DELETED = 92;

    const TYPE_EMPLOYEE_BOARD_NOTIFICATION_SETTINGS_UPDATED = 100;
    const TYPE_EMPLOYEE_BOARD_NOTIFICATION_SETTINGS_CREATED = 101;

    protected $dates = [self::DELETED_AT];
    protected $table = self::TABLE_NAME;
    protected $cascadeDeletes = [self::NOTIFICATION_EMPLOYEE_RELATION_NAME];
    protected $guarded = [];

    public static function createLog(
        ?int $userId,
        int $logType,
        string $description,
        ?int $targetedEmployeeId,
        int $loggableId,
        string $loggableType
    ) {

        $log = LogsRepository::create([
            self::USER_ID => $userId,
            self::LOG_TYPE => $logType,
            self::DESCRIPTION => $description,
            self::TARGETED_EMPLOYEE_ID => $targetedEmployeeId,
            self::LOGGABLE_ID => $loggableId,
            self::LOGGABLE_TYPE=> $loggableType,
        ]);

        (new NotifyEmployeesAction([NotifyEmployeesAction::LOG => $log]))->run();

        return $log;
    }

    public static function returnLogGroup(string $logGroup): array
    {
        switch ($logGroup) {
            case self::EMPLOYEE_GROUP:
                return [
                    Log::TYPE_EMPLOYEE_CREATED,
                    Log::TYPE_EMPLOYEE_UPDATED,
                    Log::TYPE_EMPLOYEE_DELETED,
                ];

            case self::TASK_STATUS_GROUP:
                return [
                    Log::TYPE_CARD_ACTIVATED,
                    Log::TYPE_CARD_CANCELLED,
                    Log::TYPE_CARD_COMPLETED
                ];

            case self::TASK_MOVEMENT_GROUP:
                return [
                    Log::TYPE_CARD_PLACED,
                    Log::TYPE_CARD_MOVED,
                ];

            case self::TASK_INFORMATION_UPDATE_GROUP:
                return [
                    Log::TYPE_CARD_UPDATED,
                    Log::TYPE_CARD_ASSIGNED_GROUP,
                    Log::TYPE_CARD_FILE_REMOVED,
                    Log::TYPE_CARD_FILE_ADDED
                ];

            case self::TASK_CHECKLIST_GROUP:
                return [
                    Log::TYPE_CARD_CHECKLIST_ITEM_CHECKED,
                    Log::TYPE_CARD_CHECKLIST_ITEM_UNCHECKED
                ];

            case self::TASK_ASSIGNATION_TO_USER_GROUP:
                return [
                    Log::TYPE_CARD_ASSIGNED_TO_USER,
                    Log::TYPE_CARD_UNASSIGNED_TO_USER
                ];

            case self::BOARD_MEMBER_GROUP:
                return [
                    Log::TYPE_KANBAN_MEMBER_CREATED,
                    Log::TYPE_KANBAN_MEMBER_DELETED
                ];

            case self::COMMENT_GROUP:
                return [
                    Log::TYPE_COMMENT_EDITED,
                    Log::TYPE_COMMENT_CREATED,
                    LOG::TYPE_COLUMN_DELETED
                ];

            case self::MENTIONS_GROUP:
                return [
                    Log::TYPE_COMMENT_MENTION_CREATED
                ];
        }
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function taskVersion(): HasOne
    {
        return $this->hasOne(TaskVersion::class);
    }

    public function targetEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, self::TARGETED_EMPLOYEE_ID);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([User::FIRST_NAME => 'DELETED', User::LAST_NAME=> 'USER']);
    }

    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, self::KANBAN_EMPLOYEE_LOG_TABLE_NAME, 'log_id', 'employee_id')->withTimestamps();
    }
}
