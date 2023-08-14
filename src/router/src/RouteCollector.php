<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Handler;
use Hyperf\Server\Server;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Foundation\Router\Exceptions\RouteInvalidException;
use SwooleTW\Hyperf\Foundation\Router\Exceptions\RouteNotFoundException;

class RouteCollector
{
    protected array $routes = [];

    public function __construct(
        protected ContainerInterface $container,
        protected DispatcherFactory $factory
    ) {
        $this->collectRoutes();
    }

    protected function collectRoutes(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $servers = $config->get('server.servers', []);

        foreach ($servers as $server) {
            if (Arr::get($server, 'type') !== Server::SERVER_HTTP
                || ! isset($server['name'])) {
                continue;
            }
            $serverName = $server['name'];
            [$data, $dynamic] = $this->factory->getRouter($serverName)->getData();
            $this->addRoutes($serverName, $data);
            $this->addDynamicRoutes($serverName, $dynamic);
        }
    }

    public function addRoute(string $server, string $name, string $route)
    {
        $this->routes[$server][$name] = $route;
    }

    public function getRoute(string $server, string $name): ?string
    {
        return $this->routes[$server][$name] ?? null;
    }

    public function getPath(string $name, array $variables = [], string $server = 'http')
    {
        $router = $this->factory->getRouter($server);
        $route = $this->getRoute($server, $name);
        if ($route === null) {
            throw new RouteNotFoundException(sprintf('Route name %s is not found in server %s.', $name, $server));
        }

        $result = $router->getRouteParser()->parse($route);
        foreach ($result as $items) {
            $path = '';
            $vars = $variables;
            foreach ($items as $item) {
                if (is_array($item)) {
                    [$key] = $item;
                    if (! isset($vars[$key])) {
                        $path = null;
                        break;
                    }
                    $path .= $vars[$key];
                    unset($vars[$key]);
                } else {
                    $path .= $item;
                }
            }

            if (empty($vars) && $path !== null) {
                return $path;
            }
        }

        throw new RouteInvalidException('Route is invliad.');
    }

    protected function addRoutes(string $serverName, array $data = []): void
    {
        foreach ($data as $method => $handlers) {
            foreach ($handlers as $handler) {
                if ($handler instanceof Handler) {
                    $name = $handler->options['name'] ?? $handler->route;
                    $this->addRoute($serverName, $name, $handler->route);
                }
            }
        }
    }

    protected function addDynamicRoutes(string $serverName, array $data = []): void
    {
        foreach ($data as $method => $routes) {
            foreach ($routes as $route) {
                foreach ($route['routeMap'] as [$handler, $variables]) {
                    if ($handler instanceof Handler) {
                        $name = $handler->options['name'] ?? $handler->route;
                        $this->addRoute($serverName, $name, $handler->route);
                    }
                }
            }
        }
    }
}
