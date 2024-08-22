<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Router\DispatcherFactory;

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
class Router
{
    protected string $serverName = 'http';

    public function __construct(protected DispatcherFactory $dispatcherFactory)
    {
    }

    public function addServer(string $serverName, callable $callback): void
    {
        $this->serverName = $serverName;
        $callback();
        $this->serverName = 'http';
    }

    public function __call(string $name, array $arguments)
    {
        return $this->dispatcherFactory
            ->getRouter($this->serverName)
            ->{$name}(...$arguments);
    }

    public function getRouter()
    {
        return $this->dispatcherFactory
            ->getRouter($this->serverName);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return ApplicationContext::getContainer()
            ->get(Router::class)
            ->{$name}(...$arguments);
    }
}
