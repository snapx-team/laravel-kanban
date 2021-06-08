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
        return User::orderBy('first_name')->get();
    }

    public function getAllJobSites()
    {
        return JobSite::orderBy('name')->get();
    }
}
