<?php

namespace Xguard\LaravelKanban\Enums;

use MyCLabs\Enum\Enum;

/**
 * @method static LoggableTypes BADGE()
 * @method static LoggableTypes TASK();
 * @method static LoggableTypes BOARD();
 * @method static LoggableTypes COMMENT();
 * @method static LoggableTypes ROW();
 * @method static LoggableTypes COLUMN();
 */

class LoggableTypes extends Enum
{
    private const BADGE = 'Xguard\LaravelKanban\Models\Badge';
    private const TASK = 'Xguard\LaravelKanban\Models\Task';
    private const BOARD = 'Xguard\LaravelKanban\Models\Board';
    private const COMMENT = 'Xguard\LaravelKanban\Models\comment';
    private const ROW = 'Xguard\LaravelKanban\Models\row';
    private const COLUMN = 'Xguard\LaravelKanban\Models\column';
}
