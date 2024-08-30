<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Cookie\Contracts\Cookie as CookieContract;
use SwooleTW\Hyperf\Cookie\Cookie as CookieInstance;
use SwooleTW\Hyperf\Cookie\CookieManager;

/**
 * @method static bool has(string $key)
 * @method static ?string get(string $key, ?string $default = null)
 * @method static CookieInstance make(string $name, string $value, int $minutes = 0, string $path = '', string $domain = '', bool $secure = false, bool $httpOnly = true, bool $raw = false, ?string $sameSite = null)
 * @method static void queue(...$parameters)
 * @method static void expire(string $name, string $path = '', string $domain = '')
 * @method static void unqueue(string $name, string $path = '')
 * @method static array getQueuedCookies()
 * @method static CookieInstance forever(string $name, string $value, string $path = '', string $domain = '', bool $secure = false, bool $httpOnly = true, bool $raw = false, ?string $sameSite = null)
 * @method static CookieInstance forget(string $name, string $path = '', string $domain = '')
 *
 * @see CookieManager
 */
class Cookie extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CookieContract::class;
    }
}
