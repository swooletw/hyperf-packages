<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use Hyperf\Context\ApplicationContext;

/**
 * Get the path by the route name.
 */
function route(string $name, array $variables = [], string $server = 'http'): string
{
    $container = ApplicationContext::getContainer();
    $collector = $container->get(RouteCollector::class);

    return $collector->getPath($name, $variables, $server);
}
