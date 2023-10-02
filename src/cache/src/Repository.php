<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use ArrayAccess;
use BadMethodCallException;
use Carbon\Carbon;
use Closure;
use DateInterval;
use DateTimeInterface;
use Hyperf\Macroable\Macroable;
use Hyperf\Support\Traits\InteractsWithTime;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Cache\Contracts\Repository as CacheContract;
use SwooleTW\Hyperf\Cache\Contracts\Store;
use SwooleTW\Hyperf\Cache\Events\CacheHit;
use SwooleTW\Hyperf\Cache\Events\CacheMissed;
use SwooleTW\Hyperf\Cache\Events\KeyForgotten;
use SwooleTW\Hyperf\Cache\Events\KeyWritten;

/**
 * @mixin \SwooleTW\Hyperf\Cache\Contracts\Store
 */
class Repository implements ArrayAccess, CacheContract
{
    use InteractsWithTime;
    use Macroable {
        __call as macroCall;
    }

    /**
     * The cache store implementation.
     *
     * @var \SwooleTW\Hyperf\Cache\Contracts\Store
     */
    protected $store;

    /**
     * The event dispatcher implementation.
     *
     * @var EventDispatcherInterface
     */
    protected $events;

    /**
     * The default number of seconds to store items.
     *
     * @var null|int
     */
    protected $default = 3600;

    /**
     * Create a new cache repository instance.
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Handle dynamic calls into macros or pass missing methods to the store.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->store->{$method}(...$parameters);
    }

    /**
     * Clone cache repository instance.
     */
    public function __clone()
    {
        $this->store = clone $this->store;
    }

    /**
     * Determine if an item exists in the cache.
     */
    public function has(string $key): bool
    {
        return ! is_null($this->get($key));
    }

    /**
     * Determine if an item doesn't exist in the cache.
     *
     * @param string $key
     * @return bool
     */
    public function missing($key)
    {
        return ! $this->has($key);
    }

    /**
     * Retrieve an item from the cache by key.
     */
    public function get(string|array $key, mixed $default = null): mixed
    {
        if (is_array($key)) {
            return $this->many($key);
        }

        $value = $this->store->get($this->itemKey($key));

        // If we could not find the cache value, we will fire the missed event and get
        // the default value for this cache value. This default could be a callback
        // so we will execute the value function which will resolve it if needed.
        if (is_null($value)) {
            $this->event(new CacheMissed($key));

            $value = value($default);
        } else {
            $this->event(new CacheHit($key, $value));
        }

        return $value;
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @return array
     */
    public function many(array $keys)
    {
        $values = $this->store->many(collect($keys)->map(function ($value, $key) {
            return is_string($key) ? $key : $value;
        })->values()->all());

        return collect($values)->map(function ($value, $key) use ($keys) {
            return $this->handleManyResult($keys, $key, $value);
        })->all();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $defaults = [];

        foreach ($keys as $key) {
            $defaults[$key] = $default;
        }

        return $this->many($defaults);
    }

    /**
     * Retrieve an item from the cache and delete it.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return tap($this->get($key, $default), function () use ($key) {
            $this->forget($key);
        });
    }

    /**
     * Store an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param null|DateInterval|DateTimeInterface|int $ttl
     * @return bool
     */
    public function put($key, $value, $ttl = null)
    {
        if (is_array($key)) {
            return $this->putMany($key, $value);
        }

        if ($ttl === null) {
            return $this->forever($key, $value);
        }

        $seconds = $this->getSeconds($ttl);

        if ($seconds <= 0) {
            return $this->forget($key);
        }

        $result = $this->store->put($this->itemKey($key), $value, $seconds);
        if ($result) {
            $this->event(new KeyWritten($key, $value, $seconds));
        }

        return $result;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        return $this->put($key, $value, $ttl);
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
     *
     * @param null|DateInterval|DateTimeInterface|int $ttl
     * @return bool
     */
    public function putMany(array $values, $ttl = null)
    {
        if ($ttl === null) {
            return $this->putManyForever($values);
        }

        $seconds = $this->getSeconds($ttl);

        if ($seconds <= 0) {
            return $this->deleteMultiple(array_keys($values));
        }

        $result = $this->store->putMany($values, $seconds);

        if ($result) {
            foreach ($values as $key => $value) {
                $this->event(new KeyWritten($key, $value, $seconds));
            }
        }

        return $result;
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        return $this->putMany(is_array($values) ? $values : iterator_to_array($values), $ttl);
    }

    /**
     * Store an item in the cache if the key does not exist.
     *
     * @param string $key
     * @param mixed $value
     * @param null|DateInterval|DateTimeInterface|int $ttl
     * @return bool
     */
    public function add($key, $value, $ttl = null)
    {
        $seconds = null;

        if ($ttl !== null) {
            $seconds = $this->getSeconds($ttl);

            if ($seconds <= 0) {
                return false;
            }

            // If the store has an "add" method we will call the method on the store so it
            // has a chance to override this logic. Some drivers better support the way
            // this operation should work with a total "atomic" implementation of it.
            if (method_exists($this->store, 'add')) {
                return $this->store->add(
                    $this->itemKey($key),
                    $value,
                    $seconds
                );
            }
        }

        // If the value did not exist in the cache, we will put the value in the cache
        // so it exists for subsequent requests. Then, we will return true so it is
        // easy to know if the value gets added. Otherwise, we will return false.
        if (is_null($this->get($key))) {
            return $this->put($key, $value, $seconds);
        }

        return false;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return bool|int
     */
    public function increment($key, $value = 1)
    {
        return $this->store->increment($key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return bool|int
     */
    public function decrement($key, $value = 1)
    {
        return $this->store->decrement($key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function forever($key, $value)
    {
        $result = $this->store->forever($this->itemKey($key), $value);

        if ($result) {
            $this->event(new KeyWritten($key, $value));
        }

        return $result;
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param string $key
     * @param null|DateInterval|DateTimeInterface|int $ttl
     * @return mixed
     */
    public function remember($key, $ttl, Closure $callback)
    {
        $value = $this->get($key);

        // If the item exists in the cache we will just return this immediately and if
        // not we will execute the given Closure and cache the result of that for a
        // given number of seconds so it's available for all subsequent requests.
        if (! is_null($value)) {
            return $value;
        }

        $this->put($key, $value = $callback(), $ttl);

        return $value;
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     *
     * @param string $key
     * @return mixed
     */
    public function sear($key, Closure $callback)
    {
        return $this->rememberForever($key, $callback);
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     *
     * @param string $key
     * @return mixed
     */
    public function rememberForever($key, Closure $callback)
    {
        $value = $this->get($key);

        // If the item exists in the cache we will just return this immediately
        // and if not we will execute the given Closure and cache the result
        // of that forever so it is available for all subsequent requests.
        if (! is_null($value)) {
            return $value;
        }

        $this->forever($key, $value = $callback());

        return $value;
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     * @return bool
     */
    public function forget($key)
    {
        return tap($this->store->forget($this->itemKey($key)), function ($result) use ($key) {
            if ($result) {
                $this->event(new KeyForgotten($key));
            }
        });
    }

    public function delete(string $key): bool
    {
        return $this->forget($key);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $result = true;

        foreach ($keys as $key) {
            if (! $this->forget($key)) {
                $result = false;
            }
        }

        return $result;
    }

    public function clear(): bool
    {
        return $this->store->flush();
    }

    /**
     * Begin executing a new tags operation if the store supports it.
     *
     * @param array|mixed $names
     * @return \SwooleTW\Hyperf\Cache\TaggedCache
     * @throws BadMethodCallException
     */
    public function tags($names)
    {
        if (! $this->supportsTags()) {
            throw new BadMethodCallException('This cache store does not support tagging.');
        }

        $cache = $this->store->tags(is_array($names) ? $names : func_get_args());

        if (! is_null($this->events)) {
            $cache->setEventDispatcher($this->events);
        }

        return $cache->setDefaultCacheTime($this->default);
    }

    /**
     * Determine if the current store supports tags.
     *
     * @return bool
     */
    public function supportsTags()
    {
        return method_exists($this->store, 'tags');
    }

    /**
     * Get the default cache time.
     *
     * @return null|int
     */
    public function getDefaultCacheTime()
    {
        return $this->default;
    }

    /**
     * Set the default cache time in seconds.
     *
     * @param null|int $seconds
     * @return $this
     */
    public function setDefaultCacheTime($seconds)
    {
        $this->default = $seconds;

        return $this;
    }

    /**
     * Get the cache store implementation.
     *
     * @return \SwooleTW\Hyperf\Cache\Contracts\Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->events;
    }

    /**
     * Set the event dispatcher instance.
     */
    public function setEventDispatcher(EventDispatcherInterface $events)
    {
        $this->events = $events;
    }

    /**
     * Determine if a cached value exists.
     *
     * @param string $key
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key
     */
    public function offsetGet($key): mixed
    {
        return $this->get($key);
    }

    /**
     * Store an item in the cache for the default time.
     *
     * @param string $key
     * @param mixed $value
     */
    public function offsetSet($key, $value): void
    {
        $this->put($key, $value, $this->default);
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     */
    public function offsetUnset($key): void
    {
        $this->forget($key);
    }

    /**
     * Handle a result for the "many" method.
     *
     * @param array $keys
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function handleManyResult($keys, $key, $value)
    {
        // If we could not find the cache value, we will fire the missed event and get
        // the default value for this cache value. This default could be a callback
        // so we will execute the value function which will resolve it if needed.
        if (is_null($value)) {
            $this->event(new CacheMissed($key));

            return isset($keys[$key]) ? value($keys[$key]) : null;
        }

        // If we found a valid value we will fire the "hit" event and return the value
        // back from this function. The "hit" event gives developers an opportunity
        // to listen for every possible cache "hit" throughout this applications.
        $this->event(new CacheHit($key, $value));

        return $value;
    }

    /**
     * Store multiple items in the cache indefinitely.
     *
     * @return bool
     */
    protected function putManyForever(array $values)
    {
        $result = true;

        foreach ($values as $key => $value) {
            if (! $this->forever($key, $value)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Format the key for a cache item.
     *
     * @param string $key
     * @return string
     */
    protected function itemKey($key)
    {
        return $key;
    }

    /**
     * Calculate the number of seconds for the given TTL.
     *
     * @param DateInterval|DateTimeInterface|int $ttl
     * @return int
     */
    protected function getSeconds($ttl)
    {
        $duration = $this->parseDateInterval($ttl);

        if ($duration instanceof DateTimeInterface) {
            $duration = Carbon::now()->diffInRealSeconds($duration, false);
        }

        return (int) $duration > 0 ? $duration : 0;
    }

    /**
     * Fire an event for this cache instance.
     *
     * @param string $event
     */
    protected function event($event)
    {
        if (isset($this->events)) {
            $this->events->dispatch($event);
        }
    }
}
