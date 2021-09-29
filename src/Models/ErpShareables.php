<?php

namespace Xguard\LaravelKanban\Models;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class ErpShareables extends Model
{
    protected $table = 'kanban_erp_shareables';

    protected $guarded = [];

}
