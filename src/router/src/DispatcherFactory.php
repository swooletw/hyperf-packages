<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\DispatcherFactory as BaseDispatcherFactory;
use Hyperf\HttpServer\Router\RouteCollector;
use Hyperf\HttpServer\Router\Router;

class DispatcherFactory extends BaseDispatcherFactory
{
    public function __construct(protected ContainerInterface $container)
    {
        $this->routes = $container->get(RouteFileCollector::class)
            ->getRouteFiles();

        parent::__construct();
    }

    public function initConfigRoute()
    {
        MiddlewareManager::$container = [];

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
}
