<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;

use Xguard\LaravelKanban\Models\Badge;

class BadgeController extends Controller
{

    public function getAllBadges()
    {
        return Badge::all();
    }
}
