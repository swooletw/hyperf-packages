<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\Contract\SessionInterface;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static void put(string|array $key, mixed $value = null)
 * @method static bool has(string $key)
 * @method static void forget(string $key)
 * @method static void flush()
 * @method static string getId()
 * @method static void setId(string $id)
 * @method static void start()
 * @method static bool save()
 * @method static array all()
 * @method static bool exists(string $key)
 * @method static void push(string $key, mixed $value)
 * @method static mixed pull(string $key, mixed $default = null)
 * @method static bool invalidate()
 * @method static bool migrate(bool $destroy = false)
 * @method static bool isStarted()
 * @method static string|null token()
 * @method static void regenerateToken()
 * @method static string getName()
 * @method static void setName(string $name)
 *
 * @see \Hyperf\Contract\SessionInterface
 */
class Session extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SessionInterface::class;
    }
}
