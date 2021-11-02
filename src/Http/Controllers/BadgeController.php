<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Xguard\LaravelKanban\Actions\Badges\CreateBadgeAction;
use Xguard\LaravelKanban\Actions\Badges\EditBadgeAction;
use Xguard\LaravelKanban\Actions\Badges\DeleteBadgeAction;
use Xguard\LaravelKanban\Actions\Badges\ListBadgesWithCountAction;
use Xguard\LaravelKanban\Models\Badge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{

    public function getAllBadges()
    {
        return Badge::all();
    }

    public function listBadgesWithCount()
    {
        try {
            $badgeList = app(ListBadgesWithCountAction::class)->run();
            return response([
                'success' => 'true',
                'data' => $badgeList
            ], 200);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function deleteBadge($id)
    {
        try {
            app(DeleteBadgeAction::class)->fill(['badge_id' => $id])->run();
            return response(['success' => 'true'], 200);
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function createOrUpdateBadge(Request $request)
    {
        if ($request->filled('id')) {
            app(EditBadgeAction::class)->fill(['badge_id' => $request->get('id'), 'name' => $request->get('name')])->run();
        } else {
            app(CreateBadgeAction::class)->fill(['name' => $request->get('name')])->run();
        }
        return response(['success' => 'true'], 200);
    }
}
