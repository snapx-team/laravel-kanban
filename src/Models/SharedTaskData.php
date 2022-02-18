<?php

namespace Xguard\LaravelKanban\Models;

use App\Models\Contract;
use App\Models\User;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SharedTaskData extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'kanban_shared_task_data';
    protected $cascadeDeletes = ['erpContracts', 'erpEmployees'];
    protected $guarded = [];

    const KANBAN_ERP_SHAREABLES_TABLE_NAME = 'kanban_erp_shareables';

    const ID = 'id';
    const DESCRIPTION = 'description';
    const SHAREABLE_ID = 'shareable_id';
    const SHAREABLE_TYPE = 'shareable_type';
    const CONTRACT = 'contract';
    const USER = 'user';

    const ERP_CONTRACTS_RELATION_NAME = 'erpContracts';
    const ERP_EMPLOYEES_RELATION_NAME = 'erpEmployees';

    public function taskCards(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy(Task::NAME, 'asc');
    }

    public function erpContracts(): BelongsToMany
    {
        return $this->belongsToMany(
            Contract::class,
            self::KANBAN_ERP_SHAREABLES_TABLE_NAME,
            Task::SHARED_TASK_DATA_RELATION_ID,
            self::SHAREABLE_ID
        )->wherePivot(self::SHAREABLE_TYPE, '=', self::CONTRACT)->withPivot(self::SHAREABLE_TYPE);
    }

    public function erpEmployees(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            self::KANBAN_ERP_SHAREABLES_TABLE_NAME,
            Task::SHARED_TASK_DATA_RELATION_ID,
            self::SHAREABLE_ID
        )->wherePivot(self::SHAREABLE_TYPE, '=', self::USER)->withPivot(self::SHAREABLE_TYPE);
    }
}
