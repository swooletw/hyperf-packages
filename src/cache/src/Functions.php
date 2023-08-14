<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use Exception;
use Hyperf\Context\ApplicationContext;
use SwooleTW\Hyperf\Cache\Exceptions\InvalidArgumentException;

/**
 * Get / set the specified cache value.
 *
 * If an array is passed, we'll assume you want to put to the cache.
 *
 * @param  dynamic  key|key,default|data,expiration|null
 * @return mixed|\SwooleTW\Hyperf\Cache\CacheManager
 * @throws Exception
 */
function cache()
{
    $arguments = func_get_args();
    $manager = ApplicationContext::getContainer()->get(CacheManager::class);

    if (empty($arguments)) {
        return $manager;
    }

    if (is_string($arguments[0])) {
        return $manager->get(...$arguments);
    }

    if (! is_array($arguments[0])) {
        throw new InvalidArgumentException(
            'When setting a value in the cache, you must pass an array of key / value pairs.'
        );
    }

    return $manager->put(key($arguments[0]), reset($arguments[0]), $arguments[1] ?? null);
}
