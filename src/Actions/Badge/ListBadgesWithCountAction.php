<?php

namespace Xguard\LaravelKanban\Actions\Badge;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Action;
use stdClass;
use Xguard\LaravelKanban\Models\Badge;

class ListBadgesWithCountAction extends Action
{
    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $badges_count_tasks = DB::table('kanban_badges')
            ->whereNull('kanban_badges.deleted_at')
            ->whereNull('kanban_tasks.deleted_at')
            ->leftJoin('kanban_tasks', 'kanban_tasks.badge_id', '=', 'kanban_badges.id')
            ->select('kanban_badges.id','kanban_badges.name', DB::raw('count(kanban_tasks.id) as total'))
            ->groupBy('kanban_badges.id')
            ->get();

        $badges_count_template = DB::table('kanban_badges')
            ->whereNull('kanban_badges.deleted_at')
            ->whereNull('kanban_templates.deleted_at')
            ->leftJoin('kanban_templates', 'kanban_templates.badge_id', '=', 'kanban_badges.id')
            ->select('kanban_badges.id','kanban_badges.name', DB::raw('count(kanban_templates.id) as total'))
            ->groupBy('kanban_badges.id')
            ->get();

        $badge_ids_from_tasks = $badges_count_tasks->pluck('id')->toArray();
        $validBadgesNotInTasks = Badge::query()->whereNotIn('id', $badge_ids_from_tasks)->get();

        if ($validBadgesNotInTasks->count()) {
            foreach($validBadgesNotInTasks as $badge) {
                $item = new stdClass;
                $item->id = $badge->id;
                $item->name = $badge->name;
                $item->total = 0;
                $badges_count_tasks->push($item);
            }
        }

        $badges_count_tasks = $badges_count_tasks->keyBy('id');
        $badges_count_template = $badges_count_template->keyBy('id');

        $badge_totals  = $badges_count_tasks->map(function ($item, $key) use($badges_count_template) {
            $item->total = $item->total + ($badges_count_template->get($key) ? $badges_count_template->get($key)->total : 0);
            return $item;
        });

        $sorted = $badge_totals->sortBy('name')->values();

        return $sorted;
    }
}
