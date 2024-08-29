<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Support\Environment as Accessor;

/**
 * @method static string|null get()
 * @method static static set(string $env)
 * @method static static setDebug(bool $debug)
 * @method static bool is(string|string[] ...$environments)
 * @method static bool isDebug()
 * @method static bool isTesting()
 * @method static bool isLocal()
 * @method static bool isDevelop()
 * @method static bool isProduction()
 *
 * @see Accessor
 */
class Environment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
