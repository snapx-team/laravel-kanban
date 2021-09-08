<?php

namespace Xguard\LaravelKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string|null $options
 */

class Template extends Model
{
    protected $table = 'kanban_templates';
    protected $guarded = [];

    protected $appends = [
        'unserialized_options',
    ];
    public function getUnserializedOptionsAttribute()
    {
        return unserialize($this->options);
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function boards(): BelongsToMany
    {
        return $this->belongsToMany(Board::class, 'kanban_board_template', 'template_id', 'board_id');
    }
}
