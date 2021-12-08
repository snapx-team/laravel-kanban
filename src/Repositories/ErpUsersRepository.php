<?php

namespace Xguard\LaravelKanban\Repositories;

use Xguard\LaravelKanban\Entities\ErpUser;
use App\Models\User;
use Illuminate\Support\Collection;

class ErpUsersRepository
{
    public static function retrieve(int $erpUserId): ?ErpUser
    {
        $erpUser = User::find($erpUserId);
        return $erpUser ? new ErpUser($erpUser->id, $erpUser->first_name, $erpUser->last_name) : null;
    }

    public static function getSomeUsers($search): Collection
    {
        $erpUsers = User::where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
            ->orWhere('last_name', 'like', "%{$search}%")
            ->orWhere(User::raw('CONCAT(first_name, " ", last_name)'), 'like', "%{$search}%");
        })->orderBy('first_name')->take(10)->get();

        return $erpUsers->map(function($erpUser) {
            return new ErpUser($erpUser->id, $erpUser->first_name, $erpUser->last_name);
        });
    }

    public static function getAllUsers(): Collection
    {
        $erpUsers = User::orderBy('first_name')->take(10)->get();

        return $erpUsers->map(function($erpUser) {
            return new ErpUser($erpUser->id, $erpUser->first_name, $erpUser->last_name);
        });
    }
}
