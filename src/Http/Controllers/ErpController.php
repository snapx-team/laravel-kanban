<?php

namespace Xguard\LaravelKanban\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Xguard\LaravelKanban\Repositories\ErpContractsRepository;
use Xguard\LaravelKanban\Repositories\ErpUsersRepository;

class ErpController extends Controller
{

    public function getAllUsers(): Collection
    {
        return ErpUsersRepository::getAllUsers();
    }

    public function getSomeUsers($search): Collection
    {
        return ErpUsersRepository::getSomeUsers($search);
    }

    public function getAllContracts(): Collection
    {
        return ErpContractsRepository::getAllContracts();
    }

    public function getSomeContracts($search): Collection
    {
        return ErpContractsRepository::getSomeContracts($search);
    }
}
