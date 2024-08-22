<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Router\Router;

/**
 * @method static void addRoute(array|string $httpMethod, string $route, mixed $handler, array $options = [])
 * @method static void group($prefix, callable $callback, array $options = [])
 * @method static void match($methods, $route, $handler, array $options = [])
 * @method static void any($route, $handler, array $options = [])
 * @method static void get($route, $handler, array $options = [])
 * @method static void post($route, $handler, array $options = [])
 * @method static void put($route, $handler, array $options = [])
 * @method static void delete($route, $handler, array $options = [])
 * @method static void patch($route, $handler, array $options = [])
 * @method static void head($route, $handler, array $options = [])
 * @method static void options($route, $handler, array $options = [])
 */
class Route extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Router::class;
    }
}
