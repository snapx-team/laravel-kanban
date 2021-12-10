<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\HTTP\Response;
use Xguard\LaravelKanban\Actions\Members\CreateMembersAction;
use Xguard\LaravelKanban\Actions\ErpShareables\DeleteMemberAction;
use Xguard\LaravelKanban\Repositories\MembersRepository;

class MemberController extends Controller
{
    public function createMembers(Request $request, $boardId): Response
    {
        $employees = $request->all();
        try {
            app(CreateMembersAction::class)->fill([
                'employees' => $employees,
                'boardId' => $boardId
            ])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function deleteMember($id): Response
    {
        try {
            app(DeleteMemberAction::class)->fill(['memberId' => $id])->run();
        } catch (\Exception $e) {
            return response([
                'success' => 'false',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response(['success' => 'true'], 200);
    }

    public function getMembers($id): Collection
    {
        return MembersRepository::getMembers($id);
    }

    public function getMembersFormattedForQuill($id): Collection
    {
        return MembersRepository::getMembersFormattedForQuill($id);
    }
}
