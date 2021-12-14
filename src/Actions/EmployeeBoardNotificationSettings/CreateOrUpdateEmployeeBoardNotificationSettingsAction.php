<?php

namespace Xguard\LaravelKanban\Actions\EmployeeBoardNotificationSettings;

use DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Xguard\LaravelKanban\Models\Log;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Repositories\EmployeeBoardNotificationSettingRepository;

class CreateOrUpdateEmployeeBoardNotificationSettingsAction extends Action
{

    /**
     * Execute the action and return a result.
     *
     * @return void
     * @throws Exception|Throwable
     */

    public function handle()
    {
        try {
            DB::beginTransaction();


            foreach ($this->notificationSettings as $boardId => $notificationSetting) {
                $notificationSettings = EmployeeBoardNotificationSettingRepository::updateOrCreate(
                    [
                        'employee_id' => session('employee_id'),
                        'board_id' => $boardId
                    ],
                    [
                        'ignore_types' => serialize($notificationSetting)
                    ]
                );

                if ($notificationSettings->wasChanged()) {
                    // updateOrCreate performed an update
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_EMPLOYEE_BOARD_NOTIFICATION_SETTINGS_UPDATED,
                        'updated notification settings',
                        null,
                        $notificationSettings->id,
                        'Xguard\LaravelKanban\Models\EmployeeBoardNotificationSetting'
                    );
                }

                if ($notificationSettings->wasRecentlyCreated) {
                    // updateOrCreate performed create
                    Log::createLog(
                        Auth::user()->id,
                        Log::TYPE_EMPLOYEE_BOARD_NOTIFICATION_SETTINGS_CREATED,
                        'created notification settings',
                        null,
                        $notificationSettings->id,
                        'Xguard\LaravelKanban\Models\EmployeeBoardNotificationSetting'
                    );
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
