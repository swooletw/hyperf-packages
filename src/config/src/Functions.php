<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Config;

use Hyperf\Context\ApplicationContext;
use SwooleTW\Hyperf\Config\Contracts\Repository as ConfigContract;

/**
 * Get / set the specified configuration value.
 *
 * If an array is passed as the key, we will assume you want to set an array of values.
 *
 * @param  array<string, mixed>|string|null  $key
 * @param null|string $default
 * @return ($key is null ? \SwooleTW\Hyperf\Config\Contracts\Repository : ($key is string ? mixed : null))
 */
function config(mixed $key = null, mixed $default = null): mixed
{
    $config = ApplicationContext::getContainer()->get(ConfigContract::class);

    if (is_null($key)) {
        return $config;
    }

    if (is_array($key)) {
        return $config->set($key);
    }

    return $config->get($key, $default);
}
