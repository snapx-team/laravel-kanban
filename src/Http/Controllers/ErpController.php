<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\JsonResponse;
use App\Models\User;
use App\Models\Contract;
use Xguard\LaravelKanban\Actions\Users\GetUserProfileAction;

class ErpController extends Controller
{
    public function getUserProfile()
    {
        try {
            $profile = (new GetUserProfileAction)->run();
            return new JsonResponse($profile);
        }
        catch (\Exception $e) {
            return new JsonResponse(null, 404, 'The user profile couldn\'t be retrieved');
        }
    }

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
        })->orderBy('first_name')->take(10)->get();
    }

    public function getAllContracts()
    {
        return Contract::orderBy('contract_identifier')->take(10)->get();
    }

    public function getSomeContracts($search)
    {
        return Contract::where(function ($q) use ($search) {
            $q->where('contract_identifier', 'like', "%{$search}%");
        })->orderBy('contract_identifier')->take(10)->get();
    }
}
