<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use Hyperf\Redis\RedisFactory;
use SwooleTW\Hyperf\Cache\Contracts\LockProvider;

class RedisStore extends TaggableStore implements LockProvider
{
    /**
     * @var RedisFactory
     */
    protected $factory;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * The Redis connection instance that should be used to manage locks.
     *
     * @var string
     */
    protected $connection;

    /**
     * The name of the connection that should be used for locks.
     *
     * @var string
     */
    protected $lockConnection;

    /**
     * Create a new Redis store.
     *
     * @param string $prefix
     * @param string $connection
     */
    public function __construct(RedisFactory $factory, $prefix = '', $connection = 'default')
    {
        $this->factory = $factory;
        $this->setPrefix($prefix);
        $this->setConnection($connection);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param array|string $key
     * @return mixed
     */
    public function get($key)
    {
        $value = $this->connection()->get($this->prefix . $key);

        return $this->unserialize($value);
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
        $results = [];

        $values = $this->connection()->mget(array_map(function ($key) {
            return $this->prefix . $key;
        }, $keys));

        foreach ($values as $index => $value) {
            $results[$keys[$index]] = $this->unserialize($value);
        }

        return $results;
    }

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param string $key
     * @param mixed $value
     * @param int $seconds
     * @return bool
     */
    public function put($key, $value, $seconds)
    {
        return (bool) $this->connection()->setex(
            $this->prefix . $key,
            (int) max(1, $seconds),
            $this->serialize($value)
        );
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
     *
     * @param int $seconds
     * @return bool
     */
    public function putMany(array $values, $seconds)
    {
        $this->connection()->multi();

        $manyResult = null;

        foreach ($values as $key => $value) {
            $result = $this->put($key, $value, $seconds);

            $manyResult = is_null($manyResult) ? $result : $result && $manyResult;
        }

        $this->connection()->exec();

        return $manyResult ?: false;
    }

    /**
     * Store an item in the cache if the key doesn't exist.
     *
     * @param string $key
     * @param mixed $value
     * @param int $seconds
     * @return bool
     */
    public function add($key, $value, $seconds)
    {
        $lua = "return redis.call('exists',KEYS[1])<1 and redis.call('setex',KEYS[1],ARGV[2],ARGV[1])";

        return (bool) $this->connection()->eval(
            $lua,
            [
                $this->prefix . $key,
                $this->serialize($value),
                (int) max(1, $seconds),
            ],
            1
        );
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return int
     */
    public function increment($key, $value = 1)
    {
        return $this->connection()->incrby($this->prefix . $key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return $this->connection()->decrby($this->prefix . $key, $value);
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
        return (bool) $this->connection()->set($this->prefix . $key, $this->serialize($value));
    }

    /**
     * Get a lock instance.
     *
     * @param string $name
     * @param int $seconds
     * @param null|string $owner
     * @return \SwooleTW\Hyperf\Cache\Contracts\Lock
     */
    public function lock($name, $seconds = 0, $owner = null)
    {
        return new RedisLock($this->lockConnection(), $this->prefix . $name, $seconds, $owner);
    }

    /**
     * Restore a lock instance using the owner identifier.
     *
     * @param string $name
     * @param string $owner
     * @return \SwooleTW\Hyperf\Cache\Contracts\Lock
     */
    public function restoreLock($name, $owner)
    {
        return $this->lock($name, 0, $owner);
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     * @return bool
     */
    public function forget($key)
    {
        return (bool) $this->connection()->del($this->prefix . $key);
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        $this->connection()->flushdb();

        return true;
    }

    /**
     * Begin executing a new tags operation.
     *
     * @param array|mixed $names
     * @return \SwooleTW\Hyperf\Cache\RedisTaggedCache
     */
    public function tags($names)
    {
        return new RedisTaggedCache(
            $this,
            new TagSet($this, is_array($names) ? $names : func_get_args())
        );
    }

    /**
     * Get the Redis connection instance.
     *
     * @return \Hyperf\Redis\RedisProxy
     */
    public function connection()
    {
        return $this->factory->get($this->connection);
    }

    /**
     * Get the Redis connection instance that should be used to manage locks.
     *
     * @return \Hyperf\Redis\RedisProxy
     */
    public function lockConnection()
    {
        return $this->factory->get($this->lockConnection ?? $this->connection);
    }

    /**
     * Specify the name of the connection that should be used to store data.
     *
     * @param string $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Specify the name of the connection that should be used to manage locks.
     *
     * @param string $connection
     * @return $this
     */
    public function setLockConnection($connection)
    {
        $this->lockConnection = $connection;

        return $this;
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Get the Redis database instance.
     *
     * @return RedisFactory
     */
    public function getRedis()
    {
        return $this->factory;
    }

    /**
     * Set the cache key prefix.
     *
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = ! empty($prefix) ? $prefix . ':' : '';
    }

    /**
     * Serialize the value.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function serialize($value)
    {
        // is_nan() doesn't work in strict mode
        return is_numeric($value) && ! in_array($value, [INF, -INF]) && ($value === $value) ? $value : serialize($value);
    }

    /**
     * Unserialize the value.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        if ($value === null || $value === false) {
            return null;
        }
        return is_numeric($value) ? $value : unserialize((string) $value);
    }
}
