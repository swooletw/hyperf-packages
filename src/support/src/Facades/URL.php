<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Router\UrlGenerator as Accessor;

/**
 * @mixin Accessor
 */
class URL extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
