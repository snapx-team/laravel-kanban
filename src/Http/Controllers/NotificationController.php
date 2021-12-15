<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\HTTP\Response;
use Xguard\LaravelKanban\Actions\Notifications\CreateOrUpdateEmployeeBoardNotificationSettingsAction;
use Illuminate\Database\Eloquent\Collection;
use Xguard\LaravelKanban\Repositories\BoardsRepository;
use Xguard\LaravelKanban\Repositories\EmployeesRepository;
use Xguard\LaravelKanban\Repositories\NotificationsRepository;
use DateTime;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function getNotificationData($logType): Collection
    {
        return NotificationsRepository::getNotificationData(Auth::user()->id, $logType);
    }

    public function getBoardsWithEmployeeNotificationSettings(): Collection
    {
        return BoardsRepository::getBoardsWithEmployeeNotificationSettings(session('employee_id'));
    }

    public function firstOrCreateEmployeeNotificationSettings(Request $request): Response
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

    public function getNotificationCount(): int
    {
        return EmployeesRepository::getNotificationCount(Auth::user()->id);
    }

    public function updateLastNotificationCheck(): Response
    {
        try {
            EmployeesRepository::updateLastNotificationCheck(Auth::user()->id, new DateTime('NOW'));
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }
}
