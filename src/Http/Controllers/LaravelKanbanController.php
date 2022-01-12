<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Actions\BacklogData\GetBacklogDataAction;
use Xguard\LaravelKanban\Actions\DashboardData\GetDashboardDataAction;
use Xguard\LaravelKanban\Actions\KanbanData\GetKanbanDataAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Actions\UserProfileData\GetUserProfileAction;

class LaravelKanbanController extends Controller
{
    public function getIndex()
    {
        $this->setKanbanSessionVariables();
        return view('Xguard\LaravelKanban::index');
    }

    public function setKanbanSessionVariables()
    {
        if (Auth::check()) {
            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            session(['role' => $employee->role, 'employee_id' => $employee->id]);
            return ['is_logged_in' => true];
        }
        return ['is_logged_in' => false];
    }

    public function getRoleAndEmployeeId(): array
    {
        return [
            'role' => session('role'),
            'employee_id' => session('employee_id'),
        ];
    }

    public function getFooterInfo(): array
    {
        return [
            'parent_name' => config('laravel_kanban.parent_name'),
            'version' => config('laravel_kanban.version'),
            'date' => date("Y")
        ];
    }

    public function getKanbanData($id)
    {
        try {
            return app(GetKanbanDataAction::class)->fill([
                'boardId' => $id,
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getDashboardData()
    {
        try {
            return app(GetDashboardDataAction::class)->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @throws Exception
     */
    public function getBacklogData($start, $end)
    {
        try {
            return app(GetBacklogDataAction::class)->fill([
                'start' => $start,
                'end' => $end,
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getUserProfile()
    {
        try {
            $profile = (new GetUserProfileAction)->run();
            return new JsonResponse($profile);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
