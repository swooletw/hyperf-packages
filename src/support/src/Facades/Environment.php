<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Support\Environment as Accessor;
use SwooleTW\Hyperf\Support\Facades\Facade;

/*
 * @mixin Accessor
 */
class Environment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
