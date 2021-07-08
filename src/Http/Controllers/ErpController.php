<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\JobSite;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ErpController extends Controller
{

    public function getAllUsers()
    {
        return User::orderBy('first_name')->take(10)->get();
    }

    public function getSomeUsers($search)
    {
        return User::
                where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere(User::raw('CONCAT(first_name, " ", last_name)'), 'like', "%{$search}%");
            })
            ->orderBy('first_name')->take(10)->get();
    }

    public function getAllJobSites()
    {
        return JobSite::orderBy('name')->take(10)->get();
    }

    public function getSomeJobSites($search)
    {
        return JobSite::
                where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')->take(10)->get();
    }
}
