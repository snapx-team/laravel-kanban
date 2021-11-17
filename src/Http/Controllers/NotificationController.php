<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\JsonResponse;
use DateTime;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Actions\Badges\ListBadgesWithCountAction;
use Xguard\LaravelKanban\Actions\Notifications\CreateOrUpdateEmployeeBoardNotificationSettingsAction;
use Xguard\LaravelKanban\Actions\Tasks\RemoveTaskFromGroupAction;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Comment;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\EmployeeBoardNotificationSetting;
use Xguard\LaravelKanban\Models\Template;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Actions\Users\GetUserProfileAction;

class NotificationController extends Controller
{
    public function getNotificationData($logType)
    {
        $employee = Employee::where('user_id', '=', Auth::user()->id)
            ->with('notifications.user')
            ->with(['notifications' => function ($q) use ($logType) {
                if ($logType !== "null") {
                    $q->where('log_type', (int)$logType);
                }
                $q->with('targetEmployee.user')
                    ->with(['taskVersion' => function ($q) {
                        $q->withTaskData();
                    }])
                    ->orderBy('created_at', 'desc')
                    ->with(['loggable' => function (MorphTo $morphTo) {
                        $morphTo->withoutGlobalScope(SoftDeletingScope::class)
                            ->morphWith([
                                Task::class => ['board', 'row', 'column'],
                                Comment::class => ['task.board', 'task.row', 'task.column'],
                                Board::class]);
                    }])->distinct()->paginate(10);
            }])->first();

        return $employee->notifications;
    }

    public function getBoardsWithEmployeeNotificationSettings()
    {
        return Board::orderBy('name')
            ->with('employeeNotificationSettings')
            ->whereHas('members', function ($q) {
                $q->where('employee_id', session('employee_id'));
            })
            ->with('members')->get();
    }

    public function firstOrCreateEmployeeNotificationSettings(Request $request)
    {
        try {
            (new CreateOrUpdateEmployeeBoardNotificationSettingsAction(['notificationSettings' => $request->all()]))->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function getNotificationCount()
    {
        $employee = Employee::
        where('user_id', '=', Auth::user()->id)
            ->first();
        $startDate = $employee->last_notif_check;
        if ($startDate !== null) {
            $employee = Employee::
            where('user_id', '=', Auth::user()->id)
                ->with(['notifications' => function ($q) use ($startDate) {
                    $q->where('kanban_logs.created_at', '>=', new DateTime($startDate));
                }])
                ->first();
        }
        if ($employee == null) {
            return 0;
        }
        return count($employee->notifications);
    }

    public function updateNotificationCount()
    {
        try {
            Employee::where('user_id', '=', Auth::user()->id)->update([
                'last_notif_check' => new DateTime('NOW')
            ]);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }
}
