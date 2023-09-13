<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use Hyperf\HttpServer\Router\DispatcherFactory as HyperfDispatcherFactory;
use Hyperf\HttpServer\Router\RouteCollector;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                HyperfDispatcherFactory::class => DispatcherFactory::class,
                RouteCollector::class => NamedRouteCollector::class,
            ],
        ];
    }
}
