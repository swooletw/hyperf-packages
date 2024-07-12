<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Foundation\Console\Contracts\Schedule as ScheduleContract;

/*
 * @mixin Accessor
 */

class Schedule extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ScheduleContract::class;
    }
}
