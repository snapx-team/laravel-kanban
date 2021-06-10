<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;

use Xguard\LaravelKanban\Models\Badge;

class badgeController extends Controller
{

    public function getAllBadges()
    {
        return Badge::all();
    }
}
