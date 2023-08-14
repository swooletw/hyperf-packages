<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\JWT\Contracts\ManagerContract;

/**
 * @mixin Accessor
 */
class JWT extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ManagerContract::class;
    }
}
