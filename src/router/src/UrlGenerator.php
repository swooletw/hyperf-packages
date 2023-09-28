<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use Hyperf\Context\Context;
use Hyperf\Context\RequestContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Router\DispatcherFactory;
use InvalidArgumentException;
use SwooleTW\Hyperf\Router\Contracts\UrlRoutable;

class UrlGenerator
{
    public function __construct(protected ContainerInterface $container) {}

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

            $value = $parameters[$segment[0]] ?? '';

            unset($parameters[$segment[0]]);

            return $uri . $value;
        }, '');

        $uri = $this->trimPath($uri);

        if (! empty($parameters)) {
            $uri .= '?' . http_build_query($parameters);
        }

        return $uri;
    }

    /**
     * Generate a url for the application.
     */
    public function to(string $path, array $extra = [], bool $secure = null): string
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }

        $extra = $this->formatParameters($extra);
        $tail = implode('/', array_map('rawurlencode', $extra));
        $root = $this->getRootUrl($this->getSchemeForUrl($secure));

        return $this->trimUrl($root, $path, $tail);
    }

    /**
     * Generate a secure, absolute URL to the given path.
     */
    public function secure(string $path, array $extra = []): string
    {
        return $this->to($path, $extra, true);
    }

    private function trimPath(string $path, string $tail = ''): string
    {
        return '/' . trim($path . '/' . $tail, '/');
    }

    private function trimUrl(string $root, string $path, string $tail = ''): string
    {
        return trim($root . $this->trimPath($path, $tail), '/');
    }

    private function isValidUrl($path): bool
    {
        foreach (['#', '//', 'mailto:', 'tel:', 'sms:', 'http://', 'https://'] as $value) {
            if (str_starts_with($path, $value)) {
                return true;
            }
        }

        return filter_var($path, FILTER_VALIDATE_URL) !== false;
    }

    private function formatParameters(array $parameters): array
    {
        foreach ($parameters as $key => $parameter) {
            if ($parameter instanceof UrlRoutable) {
                $parameters[$key] = $parameter->getRouteKey();
            }
        }

        return $parameters;
    }

    private function getSchemeForUrl(?bool $secure): string
    {
        if (is_null($secure)) {
            return $this->getRequestUri()->getScheme();
        }

        return $secure ? 'https' : 'http';
    }

    private function getRootUrl(string $scheme): string
    {
        $root = Context::getOrSet('__request.root.uri', function () {
            $requestUri = $this->getRequestUri()->toString();
            $root = preg_replace(';^(.+://.+?)((/|\?|#).*)?$;', '\1', $requestUri);

            return new Uri($root);
        });

        return $root->withScheme($scheme)->toString();
    }

    private function getRequestUri(): Uri
    {
        if (RequestContext::has()) {
            return $this->container->get(RequestInterface::class)->getUri();
        }

        return new Uri($this->container->get(ConfigInterface::class)->get('app.url'));
    }
}
