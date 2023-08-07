<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache\Contracts;

use Closure;
use DateInterval;
use DateTimeInterface;
use Psr\SimpleCache\CacheInterface;

interface Repository extends CacheInterface
{
    /**
     * Retrieve an item from the cache and delete it.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function pull($key, $default = null);

    /**
     * Store an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param null|DateInterval|DateTimeInterface|int $ttl
     * @return bool
     */
    public function put($key, $value, $ttl = null);

    /**
     * Store an item in the cache if the key does not exist.
     *
     * @param string $key
     * @param mixed $value
     * @param null|DateInterval|DateTimeInterface|int $ttl
     * @return bool
     */
    public function add($key, $value, $ttl = null);

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return bool|int
     */
    public function increment($key, $value = 1);

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return bool|int
     */
    public function decrement($key, $value = 1);

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function forever($key, $value);

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param string $key
     * @param null|DateInterval|DateTimeInterface|int $ttl
     * @return mixed
     */
    public function remember($key, $ttl, Closure $callback);

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     *
     * @param string $key
     * @return mixed
     */
    public function sear($key, Closure $callback);

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     *
     * @param string $key
     * @return mixed
     */
    public function rememberForever($key, Closure $callback);

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     * @return bool
     */
    public function forget($key);

    /**
     * Get the cache store implementation.
     *
     * @return \SwooleTW\Hyperf\Cache\Contracts\Store
     */
    public function getStore();
}
