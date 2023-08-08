<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Cookie\CookieManager;

/**
 * @mixin CookieJar
 */
class Cookie extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CookieManager::class;
    }
}
