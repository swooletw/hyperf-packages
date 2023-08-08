<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Cache\Contracts\Factory;
use SwooleTW\Hyperf\Support\Facades\Facade;

/**
 * @mixin Accessor
 */
class Cache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
