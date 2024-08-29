<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Config\Contracts\Repository as ConfigContract;
use SwooleTW\Hyperf\Config\Repository;

/**
 * @method static bool has(string $key)
 * @method static mixed get(array|string $key, mixed $default = null)
 * @method static array getMany(array $keys)
 * @method static string string(string $key, mixed $default = null)
 * @method static int integer(string $key, mixed $default = null)
 * @method static float float(string $key, mixed $default = null)
 * @method static bool boolean(string $key, mixed $default = null)
 * @method static array array(string $key, mixed $default = null)
 * @method static void set(array|string $key, mixed $value = null)
 * @method static void prepend(string $key, mixed $value)
 * @method static void push(string $key, mixed $value)
 * @method static array all()
 * @method static bool offsetExists(string $key)
 * @method static mixed offsetGet(string $key)
 * @method static void offsetSet(string $key, mixed $value)
 * @method static void offsetUnset(string $key)
 *
 * @see Repository
 */
class Config extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ConfigContract::class;
    }
}
