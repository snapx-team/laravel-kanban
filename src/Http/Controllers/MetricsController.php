<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Xguard\LaravelKanban\Actions\Metrics\GetAverageTimeToCompletionByBadgeAction;
use Xguard\LaravelKanban\Actions\Metrics\GetAverageTimeToCompletionByEmployeeAction;
use Xguard\LaravelKanban\Actions\Metrics\GetBadgeDataAction;
use Xguard\LaravelKanban\Actions\Metrics\GetClosedTasksByAdminAction;
use Xguard\LaravelKanban\Actions\Metrics\GetClosedTasksByAssignedToAction;
use Xguard\LaravelKanban\Actions\Metrics\GetContractDataAction;
use Xguard\LaravelKanban\Actions\Metrics\GetCreatedVsResolvedAction;
use Xguard\LaravelKanban\Actions\Metrics\GetEstimatedHoursCompletedByEmployeesAction;
use Xguard\LaravelKanban\Actions\Metrics\GetPeakHoursTasksCreatedAction;
use Xguard\LaravelKanban\Actions\Metrics\GetTasksCreatedByEmployeeAction;

class MetricsController extends Controller
{
    /**
     * @throws Exception
     */
    public function getBadgeData($start, $end)
    {
        try {
            $data = app(GetBadgeDataAction::class)->fill(['start' => $start, 'end' => $end, ])->run();
            return response(['success' => 'true', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }

    /**
     * @throws Exception
     */
    public function getContractData($start, $end)
    {
        try {
            $data = app(GetContractDataAction::class)->fill(['start' => $start, 'end' => $end, ])->run();
            return response(['success' => 'true', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }

    /**
     * @throws Exception
     */
    public function getTasksCreatedByEmployee($start, $end)
    {
        try {
            $data = app(GetTasksCreatedByEmployeeAction::class)->fill(['start' => $start, 'end' => $end, ])->run();
            return response(['success' => 'true', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }

    /**
     * @throws Exception
     */

    public function getEstimatedHoursCompletedByEmployees($start, $end)
    {
        try {
            $data = app(GetEstimatedHoursCompletedByEmployeesAction::class)->fill(['start' => $start, 'end' => $end, ])->run();
            return response(['success' => 'true', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }

    /**
     * @throws Exception
     */
    public function getPeakHoursTasksCreated($start, $end)
    {
        try {
            $data = app(GetPeakHoursTasksCreatedAction::class)->fill(['start' => $start, 'end' => $end, ])->run();
            return response(['success' => 'true', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }

    /**
     * @throws Exception
     */
    public function getClosedTasksByAssignedTo($start, $end)
    {
        try {
            $data = app(GetClosedTasksByAssignedToAction::class)->fill(['start' => $start, 'end' => $end, ])->run();
            return response(['success' => 'true', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }

    /**
     * @throws Exception
     */
    public function getClosedTasksByAdmin($start, $end)
    {
        try {
            $data = app(GetClosedTasksByAdminAction::class)->fill(['start' => $start, 'end' => $end, ])->run();
            return response(['success' => 'true', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }

    /**
     * @throws Exception
     */
    public function getAverageTimeToCompletionByBadge($start, $end)
    {
        try {
            $data = app(GetAverageTimeToCompletionByBadgeAction::class)->fill(['start' => $start, 'end' => $end, ])->run();
            return response(['success' => 'true', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }

    /**
     * @throws Exception
     */
    public function getAverageTimeToCompletionByEmployee($start, $end)
    {
        try {
            $data = app(GetAverageTimeToCompletionByEmployeeAction::class)->fill(['start' => $start, 'end' => $end, ])->run();
            return response(['success' => 'true', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }

    /**
     * @throws Exception
     */
    public function getCreatedVsResolved($start, $end)
    {
        try {
            $data = app(GetCreatedVsResolvedAction::class)->fill(['start' => $start, 'end' => $end, ])->run();
            return response(['success' => 'true', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 400);
        }
    }
}
