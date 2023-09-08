<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Routing;

use Closure;
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

        [$handler, $options] = $this->parseHandlerAndOptions($handler, $options);

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

    protected function mergeOptions(array $origin, array $options): array
    {
        if (isset($origin['as'])) {
            $options['as'] = $origin['as'] . (isset($options['as']) ? '.' . $options['as'] : '');
        }

        unset($origin['as']);

        return array_merge_recursive($origin, $options);
    }

    private function parseHandlerAndOptions(mixed $handler, array $options): array
    {
        if (! is_array($handler) || ! empty($options)) {
            return [$handler, $options];
        }

        $options = $handler;
        $handler = $this->parseAction($options);
        $options = $this->cleanOptions($options);

        return [$handler, $options];
    }

    private function parseAction(array $options): mixed
    {
        if (isset($options['uses'])) {
            return $options['uses'];
        }

        if (isset($options[0]) && $options[0] instanceof Closure) {
            return $options[0];
        }
    }

    private function cleanOptions(array $options): array
    {
        return array_diff_key($options, array_flip([0, 'uses']));
    }
}
