<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Routing;

use Hyperf\Context\ApplicationContext;
use SwooleTW\Hyperf\Routing\UrlGenerator;

/**
 * Get the URL to a named route.
 *
 * @throws InvalidArgumentException
 */
function route(string $name, array $parameters = [], string $server = 'http'): string
{
    return ApplicationContext::getContainer()->get(UrlGenerator::class)->route($name, $parameters, $server);
}
