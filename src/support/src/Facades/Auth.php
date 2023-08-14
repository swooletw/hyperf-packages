<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Auth\Contracts\FactoryContract;

/**
 * @mixin Accessor
 */
class Auth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FactoryContract::class;
    }
}
