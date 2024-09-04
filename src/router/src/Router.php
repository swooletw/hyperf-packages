<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use Closure;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Router\DispatcherFactory;
use RuntimeException;

/**
 * @method static void addRoute(array|string $httpMethod, string $route, mixed $handler, array $options = [])
 * @method static void group($prefix, string|callable $source, $callback, array $options = [])
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

    public function group($prefix, callable|string $source, array $options = []): void
    {
        if (is_string($source)) {
            $source = $this->registerRouteFile($source);
        }

        $this->dispatcherFactory
            ->getRouter($this->serverName)
            ->addGroup($prefix, $source, $options);
    }

    public function addGroup($prefix, callable|string $source, array $options = []): void
    {
        $this->group($prefix, $source, $options);
    }

    protected function registerRouteFile(string $routeFile): Closure
    {
        if (! file_exists($routeFile)) {
            throw new RuntimeException("Route file does not exist at path `{$routeFile}`.");
        }

        return fn () => require $routeFile;
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
