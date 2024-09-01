<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Router\UrlGenerator;

/**
 * @method static string route(string $name, array $parameters = [], string $server = 'http')
 * @method static string to(string $path, array $extra = [], ?bool $secure = null)
 * @method static string secure(string $path, array $extra = [])
 * @method static string full()
 * @method static string current()
 *
 * @see UrlGenerator
 */
class URL extends Facade
{
    protected static function getFacadeAccessor()
    {
        return UrlGenerator::class;
    }
}
