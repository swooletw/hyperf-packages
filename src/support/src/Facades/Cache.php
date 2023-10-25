<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Closure;
use DateInterval;
use DateTimeInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Cache\CacheManager;
use SwooleTW\Hyperf\Cache\Contracts\Factory;
use SwooleTW\Hyperf\Cache\Contracts\Lock;
use SwooleTW\Hyperf\Cache\Contracts\Repository as RepositoryContract;
use SwooleTW\Hyperf\Cache\Contracts\Store;
use SwooleTW\Hyperf\Cache\Repository;
use SwooleTW\Hyperf\Cache\TaggedCache;

/**
 * @method static RepositoryContract store(string|null $name = null)
 * @method static RepositoryContract driver(string|null $driver = null)
 * @method static Repository repository(Store $store)
 * @method static void refreshEventDispatcher()
 * @method static string getDefaultDriver()
 * @method static void setDefaultDriver(string $name)
 * @method static CacheManager forgetDriver(array|string|null $name = null)
 * @method static void purge(string|null $name = null)
 * @method static CacheManager extend(string $driver, Closure $callback)
 * @method static bool has(string $key)
 * @method static bool missing(string $key)
 * @method static mixed get(array|string $key, mixed $default = null)
 * @method static array many(array $keys)
 * @method static iterable getMultiple(iterable $keys, mixed $default = null)
 * @method static mixed pull(string $key, mixed $default = null)
 * @method static bool put(array|string $key, mixed $value, DateTimeInterface|DateInterval|int|null $ttl = null)
 * @method static bool set(string $key, mixed $value, null|int|DateInterval $ttl = null)
 * @method static bool putMany(array $values, DateTimeInterface|DateInterval|int|null $ttl = null)
 * @method static bool setMultiple(iterable $values, null|int|DateInterval $ttl = null)
 * @method static bool add(string $key, mixed $value, DateTimeInterface|DateInterval|int|null $ttl = null)
 * @method static int|bool increment(string $key, mixed $value = 1)
 * @method static int|bool decrement(string $key, mixed $value = 1)
 * @method static bool forever(string $key, mixed $value)
 * @method static mixed remember(string $key, Closure|DateTimeInterface|DateInterval|int|null $ttl, Closure $callback)
 * @method static mixed sear(string $key, Closure $callback)
 * @method static mixed rememberForever(string $key, Closure $callback)
 * @method static bool forget(string $key)
 * @method static bool delete(string $key)
 * @method static bool deleteMultiple(iterable $keys)
 * @method static bool clear()
 * @method static TaggedCache tags(array|mixed $names)
 * @method static bool supportsTags()
 * @method static int|null getDefaultCacheTime()
 * @method static Repository setDefaultCacheTime(int|null $seconds)
 * @method static Store getStore()
 * @method static EventDispatcherInterface getEventDispatcher()
 * @method static void setEventDispatcher(EventDispatcherInterface $events)
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 * @method static mixed macroCall(string $method, array $parameters)
 * @method static bool flush()
 * @method static string getPrefix()
 * @method static Lock lock(string $name, int $seconds = 0, string|null $owner = null)
 * @method static Lock restoreLock(string $name, string $owner)
 *
 * @see CacheManager
 * @mixin Repository
 */
class Cache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
