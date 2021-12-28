<?php

namespace Xguard\LaravelKanban\Enums;

use MyCLabs\Enum\Enum;

/**
 * @method static TaskStatuses COMPLETED()
 * @method static TaskStatuses CANCELLED();
 * @method static TaskStatuses ACTIVE();

 */

class TaskStatuses extends Enum
{
    private const COMPLETED = 'completed';
    private const CANCELLED = 'cancelled';
    private const ACTIVE = 'active';
}
