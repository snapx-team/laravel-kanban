<?php

namespace Xguard\LaravelKanban\Actions\Badges;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Action;
use stdClass;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\EmployeeBoardNotificationSetting;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\Template;

class ListBadgesWithCountAction extends Action
{
    /**
     * @return mixed
     */
    public function handle()
    {
        $badgeTableName = (new Badge)->getTable();
        $taskTableName = (new Task)->getTable();
        $templateTableName = (new Template)->getTable();

        $badges_count_tasks = $this->getBadgesCountByModel($badgeTableName, $taskTableName);
        $badges_count_template = $this->getBadgesCountByModel($badgeTableName, $templateTableName);

        $badge_ids_from_tasks = $badges_count_tasks->pluck(Badge::ID)->toArray();
        $validBadgesNotInTasks = Badge::query()->whereNotIn(Badge::ID, $badge_ids_from_tasks)->get();

        if ($validBadgesNotInTasks->count()) {
            foreach ($validBadgesNotInTasks as $badge) {
                $item = new stdClass;
                $item->id = $badge->id;
                $item->name = $badge->name;
                $item->total = 0;
                $badges_count_tasks->push($item);
            }
        }

        $badges_count_tasks = $badges_count_tasks->keyBy(Badge::ID);
        $badges_count_template = $badges_count_template->keyBy(Badge::ID);

        $badge_totals  = $badges_count_tasks->map(function ($item, $key) use ($badges_count_template) {
            $item->total = $item->total + ($badges_count_template->get($key) ? $badges_count_template->get($key)->total : 0);
            return $item;
        });

        return $badge_totals->sortBy(Badge::NAME)->values();
    }

    public function getBadgesCountByModel($badgeTableName, $modelTableName)
    {
        return DB::table($badgeTableName)
            ->whereNull($badgeTableName.'.'.Badge::DELETED_AT)
            ->whereNull($modelTableName.'.'.Task::DELETED_AT)
            ->leftJoin($modelTableName, $modelTableName.'.'.Task::BADGE_ID, '=', $badgeTableName.'.'.Badge::ID)
            ->select($badgeTableName.'.'.Badge::ID, $badgeTableName.'.'.Badge::NAME, DB::raw('count('.$modelTableName.'.'. Task::ID .') as total'))
            ->groupBy($badgeTableName.'.'.Badge::ID)
            ->get();
    }
}
