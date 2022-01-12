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

    const ERP_CONTRACTS_RELATION_NAME = 'erpContracts';
    const ERP_EMPLOYEES_RELATION_NAME ='erpEmployees';

    public function taskCards(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('name', 'asc');
    }

    public function erpContracts(): BelongsToMany
    {
        return $this->belongsToMany(Contract::class, 'kanban_erp_shareables', 'shared_task_data_id', 'shareable_id')->wherePivot('shareable_type', '=', 'contract')->withPivot('shareable_type');
    }

    public function erpEmployees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'kanban_erp_shareables', 'shared_task_data_id', 'shareable_id')->wherePivot('shareable_type', '=', 'user')->withPivot('shareable_type');
    }
}
