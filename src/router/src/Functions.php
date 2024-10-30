<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use Hyperf\Context\ApplicationContext;
use InvalidArgumentException;

/**
 * Get the URL to a named route.
 *
 * @throws InvalidArgumentException
 */
function route(string $name, array $parameters = [], bool $absolute = true, string $server = 'http'): string
{
    return ApplicationContext::getContainer()->get(UrlGenerator::class)->route($name, $parameters, $absolute, $server);
}

/**
 * Generate a url for the application.
 */
function url(?string $path = null, array $extra = [], ?bool $secure = null): string|UrlGenerator
{
    $urlGenerator = ApplicationContext::getContainer()->get(UrlGenerator::class);
    if (is_null($path)) {
        return $urlGenerator;
    }

    return $urlGenerator->to($path, $extra, $secure);
}

/**
 * Generate a secure, absolute URL to the given path.
 */
function secure_url(string $path, array $extra = []): string
{
    return ApplicationContext::getContainer()->get(UrlGenerator::class)->secure($path, $extra);
}

/**
 * Generate an asset path for the application.
 */
function asset(string $path, ?bool $secure = null): string
{
    return ApplicationContext::getContainer()->get(UrlGenerator::class)->asset($path, $secure);
}
