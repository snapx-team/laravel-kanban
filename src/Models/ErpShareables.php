<?php

namespace Xguard\LaravelKanban\Models;

use Illuminate\Database\Eloquent\Model;

class ErpShareables extends Model
{
    protected $dates = ['deleted_at'];

    protected $table = 'kanban_erp_shareables';

    protected $guarded = [];

}
