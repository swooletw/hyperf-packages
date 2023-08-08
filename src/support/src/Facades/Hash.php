<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Hashing\Contracts\Hasher;
use SwooleTW\Hyperf\Support\Facades\Facade;

/**
 * @mixin Accessor
 */
class Hash extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Hasher::class;
    }
}
