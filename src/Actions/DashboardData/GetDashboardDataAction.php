<?php

namespace Xguard\LaravelKanban\Actions\DashboardData;

use DateTime;
use Exception;
use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Actions\Badges\ListBadgesWithCountAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Template;

class GetDashboardDataAction extends Action
{
    /**
     * @return array
     * @throws Exception
     */
    public function handle(): array
    {
        if (session('role') === 'admin') {
            $boards = Board::orderBy('name')->with('members')->get();
        } else {
            $boards = Board::orderBy('name')->
            whereHas('members', function ($q) {
                $q->where('employee_id', session('employee_id'));
            })->with('members')->get();
        }
        $employees = Employee::with('user')->get();
        $templates = Template::orderBy('name')->with('badge', 'boards')->get();
        $badges = app(ListBadgesWithCountAction::class)->run();

        return [
            'employees' => $employees,
            'boards' => $boards,
            'templates' => $templates,
            'badges' => $badges
        ];
    }
}
