<?php

namespace Xguard\LaravelKanban\Repositories;

use Illuminate\Support\Collection;
use Xguard\LaravelKanban\Models\Member;

class MembersRepository
{
    public static function getMembers($id): Collection
    {
        return Member::where('board_id', $id)
            ->with(['employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])->get();
    }

    public static function getMembersFormattedForQuill($id): Collection
    {
        $members = Member::where('board_id', $id)
            ->with(['employee.user' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])->get();

        $formattedBoardMembers = collect();
        foreach ($members as $member) {
            $formattedBoardMembers->push(['id' => $member->employee->id, 'value' => $member->employee->user->full_name]);
        }

        return $formattedBoardMembers;
    }
}
