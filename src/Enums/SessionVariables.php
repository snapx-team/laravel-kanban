<?php

namespace Xguard\LaravelKanban\Enums;

use MyCLabs\Enum\Enum;

/**
 * @method static SessionVariables EMPLOYEE_ID()
 * @method static SessionVariables ROLE();
 */

class SessionVariables extends Enum
{
    private const EMPLOYEE_ID = 'employee_id';
    private const ROLE = 'role';
}
