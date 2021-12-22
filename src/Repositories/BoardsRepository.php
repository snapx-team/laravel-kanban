<?php

namespace Xguard\LaravelKanban\Repositories;

use Xguard\LaravelKanban\Models\Board;
use Illuminate\Database\Eloquent\Collection;

class BoardsRepository
{
    public static function findOrFail(int $id): Board
    {
        return Board::findOrFail($id);
    }

    public static function getBoards(): Collection
    {
        if (session('role') === 'admin') {
            return Board::orderBy('name')->with('members')->get();
        } else {
            return Board::orderBy('name')->
            whereHas('members', function ($q) {
                $q->where('employee_id', session('employee_id'));
            })->with('members')->get();
        }
    }

    public static function getBoardsWithEmployeeNotificationSettings(int $employeeId): Collection
    {
        return Board::orderBy('name')
            ->with('employeeNotificationSettings')
            ->whereHas('members', function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            })
            ->with('members')->get();
    }
}
