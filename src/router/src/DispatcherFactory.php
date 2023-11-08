<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\Router\DispatcherFactory as BaseDispatcherFactory;
use Hyperf\HttpServer\Router\RouteCollector;
use Hyperf\HttpServer\Router\Router;

class DispatcherFactory extends BaseDispatcherFactory
{
    protected static array $routeFiles = [
        BASE_PATH . '/config/routes.php',
    ];

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct();
    }

    public function initConfigRoute()
    {
        $this->routes = static::$routeFiles;

        Router::init($this);

        foreach ($this->routes as $route) {
            if (file_exists($route)) {
                require $route;
            }
        }
    }

    public function getRouter(string $serverName): RouteCollector
    {
        if (isset($this->routers[$serverName])) {
            return $this->routers[$serverName];
        }

        return $this->routers[$serverName] = $this->container->make(RouteCollector::class, ['server' => $serverName]);
    }

    public static function addRouteFile(string $path): void
    {
        static::$routeFiles = array_unique(array_merge(static::$routeFiles, [$path]));
    }

    public static function setRouteFiles(array $routes): void
    {
        static::$routeFiles = $routes;
    }
}
