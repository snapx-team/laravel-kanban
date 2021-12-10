<?php

namespace Xguard\LaravelKanban\Repositories;

use App\Models\Contract;
use Illuminate\Support\Collection;
use Xguard\LaravelKanban\Entities\ErpContract;

class ErpContractsRepository
{
    public static function retrieve(int $erpContractId): ?ErpContract
    {
        $erpContract = Contract::find($erpContractId);
        return $erpContract ? new ErpContract($erpContract->id, $erpContract->contract_identifier) : null;
    }

    public static function getAllContracts(): Collection
    {
        $erpContracts = Contract::orderBy('contract_identifier')->take(10)->get();

        return $erpContracts->map(function($erpContract) {
            return new ErpContract($erpContract->id, $erpContract->contract_identifier);
        });
    }

    public static function getSomeContracts($search): Collection
    {
        $erpContracts = Contract::where(function ($q) use ($search) {
            $q->where('contract_identifier', 'like', "%{$search}%");
        })->orderBy('contract_identifier')->take(10)->get();

        return $erpContracts->map(function($erpContract) {
            return new ErpContract($erpContract->id, $erpContract->contract_identifier);
        });
    }
}
