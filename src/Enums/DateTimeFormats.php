<?php

namespace Xguard\LaravelKanban\Enums;

use MyCLabs\Enum\Enum;

/**
 * @method static DateTimeFormats DATE_FORMAT()
 * @method static DateTimeFormats TIME_FORMAT()
 * @method static DateTimeFormats DATE_TIME_FORMAT()
 * @method static DateTimeFormats PARSE_TO_ISO8601()
 */

class DateTimeFormats extends Enum
{
    public const DATE_FORMAT = 'Y-m-d';
    public const TIME_FORMAT = 'H:i:s';
    public const DATE_TIME_FORMAT = self::DATE_FORMAT . ' ' . self::TIME_FORMAT;
    public const PARSE_TO_ISO8601 = 'c';
}
