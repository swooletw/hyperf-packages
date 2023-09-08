<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Routing;

use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\Router\DispatcherFactory;
use InvalidArgumentException;

class UrlGenerator
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * Get the URL to a named route.
     *
     * @throws InvalidArgumentException
     */
    public function route(string $name, array $parameters = [], string $server = 'http'): string
    {
        $namedRoutes = $this->container->get(DispatcherFactory::class)->getRouter($server)->getNamedRoutes();

        if (! array_key_exists($name, $namedRoutes)) {
            throw new InvalidArgumentException("Route [{$name}] not defined.");
        }

        $routeData = $namedRoutes[$name];

        $uri = array_reduce($routeData, function ($uri, $segment) use (&$parameters) {
            if (! is_array($segment)) {
                return $uri . $segment;
            }

            $value = $parameters[$segment[0]];

            unset($parameters[$segment[0]]);

            return $uri . $value;
        }, '');

        $uri = $this->trimUri($uri);

        if (! empty($parameters)) {
            $uri .= '?' . http_build_query($parameters);
        }

        return $uri;
    }

    private function trimUri(string $uri): string
    {
        return '/' . trim($uri, '/');
    }
}
