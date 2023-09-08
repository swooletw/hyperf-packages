<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Routing;

use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\Handler;
use Hyperf\HttpServer\Router\RouteCollector;

class NamedRouteCollector extends RouteCollector
{
    /**
     * All of the named routes and data pairs.
     */
    protected array $namedRoutes = [];

    public function addRoute(array|string $httpMethod, string $route, mixed $handler, array $options = []): void
    {
        $route = $this->currentGroupPrefix . $route;
        $routeDataList = $this->routeParser->parse($route);
        $options = $this->mergeOptions($this->currentGroupOptions, $options);

        foreach ((array) $httpMethod as $method) {
            $method = strtoupper($method);

            foreach ($routeDataList as $routeData) {
                $this->dataGenerator->addRoute($method, $routeData, new Handler($handler, $route, $options));

                if (isset($options['as'])) {
                    $this->namedRoutes[$options['as']] = $routeData;
                }
            }

            MiddlewareManager::addMiddlewares($this->server, $route, $method, $options['middleware'] ?? []);
        }
    }

    /**
     * Get all of the defined named routes.
     */
    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }
}
