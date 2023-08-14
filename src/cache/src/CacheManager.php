<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use Closure;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\RedisFactory;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface as DispatcherContract;
use SwooleTW\Hyperf\Cache\Contracts\Factory as FactoryContract;
use SwooleTW\Hyperf\Cache\Contracts\Store;

/**
 * @mixin \SwooleTW\Hyperf\Cache\Contracts\Repository
 * @mixin TaggableStore
 */
class CacheManager implements FactoryContract
{
    /**
     * The array of resolved cache stores.
     */
    protected array $stores = [];

    /**
     * The registered custom driver creators.
     */
    protected array $customCreators = [];

    /**
     * Create a new Cache manager instance.
     */
    public function __construct(
        protected ContainerInterface $app
    ) {
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->store()->{$method}(...$parameters);
    }

    /**
     * Get a cache store instance by name, wrapped in a repository.
     *
     * @param null|string $name
     * @return \SwooleTW\Hyperf\Cache\Contracts\Repository
     */
    public function store($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->stores[$name] = $this->get($name);
    }

    /**
     * Get a cache driver instance.
     *
     * @param null|string $driver
     * @return \SwooleTW\Hyperf\Cache\Contracts\Repository
     */
    public function driver($driver = null)
    {
        return $this->store($driver);
    }

    /**
     * Create a new cache repository with the given implementation.
     *
     * @return \SwooleTW\Hyperf\Cache\Repository
     */
    public function repository(Store $store)
    {
        return tap(new Repository($store), function ($repository) {
            $this->setEventDispatcher($repository);
        });
    }

    /**
     * Re-set the event dispatcher on all resolved cache repositories.
     */
    public function refreshEventDispatcher()
    {
        array_map([$this, 'setEventDispatcher'], $this->stores);
    }

    /**
     * Get the default cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app->get(ConfigInterface::class)
            ->get('laravel_cache.default', 'file');
    }

    /**
     * Set the default cache driver name.
     *
     * @param string $name
     */
    public function setDefaultDriver($name)
    {
        $this->app->get(ConfigInterface::class)
            ->set('laravel_cache.default', $name);
    }

    /**
     * Unset the given driver instances.
     *
     * @param null|array|string $name
     * @return $this
     */
    public function forgetDriver($name = null)
    {
        $name = $name ?? $this->getDefaultDriver();

        foreach ((array) $name as $cacheName) {
            if (isset($this->stores[$cacheName])) {
                unset($this->stores[$cacheName]);
            }
        }

        return $this;
    }

    /**
     * Disconnect the given driver and remove from local cache.
     *
     * @param null|string $name
     */
    public function purge($name = null)
    {
        $name = $name ?? $this->getDefaultDriver();

        unset($this->stores[$name]);
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param string $driver
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback->bindTo($this, $this);

        return $this;
    }

    /**
     * Attempt to get the store from the local cache.
     *
     * @param string $name
     * @return \SwooleTW\Hyperf\Cache\Contracts\Repository
     */
    protected function get($name)
    {
        return $this->stores[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given store.
     *
     * @param string $name
     * @return \SwooleTW\Hyperf\Cache\Contracts\Repository
     * @throws InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Cache store [{$name}] is not defined.");
        }

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        }
        $driverMethod = 'create' . ucfirst($config['driver']) . 'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        }
        throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
    }

    /**
     * Call a custom driver creator.
     *
     * @return mixed
     */
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }

    /**
     * Create an instance of the array cache driver.
     *
     * @return \SwooleTW\Hyperf\Cache\Repository
     */
    protected function createArrayDriver(array $config)
    {
        return $this->repository(new ArrayStore($config['serialize'] ?? false));
    }

    /**
     * Create an instance of the file cache driver.
     *
     * @return \SwooleTW\Hyperf\Cache\Repository
     */
    protected function createFileDriver(array $config)
    {
        $store = make(FileStore::class, [
            'directory' => $config['path'],
            'filePermission' => $config['permission'] ?? null,
        ]);
        return $this->repository($store);
    }

    /**
     * Create an instance of the Null cache driver.
     *
     * @return \SwooleTW\Hyperf\Cache\Repository
     */
    protected function createNullDriver()
    {
        return $this->repository(new NullStore());
    }

    /**
     * Create an instance of the Redis cache driver.
     *
     * @return \SwooleTW\Hyperf\Cache\Repository
     */
    protected function createRedisDriver(array $config)
    {
        $redis = $this->app->get(RedisFactory::class);

        $connection = $config['connection'] ?? 'default';

        $store = new RedisStore($redis, $this->getPrefix($config), $connection);

        return $this->repository(
            $store->setLockConnection($config['lock_connection'] ?? $connection)
        );
    }

    /**
     * Set the event dispatcher on the given repository instance.
     */
    protected function setEventDispatcher(Repository $repository)
    {
        if (! $this->app->has(DispatcherContract::class)) {
            return;
        }

        $repository->setEventDispatcher(
            $this->app->get(DispatcherContract::class)
        );
    }

    /**
     * Get the cache prefix.
     *
     * @return string
     */
    protected function getPrefix(array $config)
    {
        return $config['prefix'] ?? $this->app->get(ConfigInterface::class)->get('laravel_cache.prefix');
    }

    /**
     * Get the cache connection configuration.
     *
     * @param string $name
     * @return array
     */
    protected function getConfig($name)
    {
        if (! is_null($name) && $name !== 'null') {
            return $this->app->get(ConfigInterface::class)->get("laravel_cache.stores.{$name}");
        }

        return ['driver' => 'null'];
    }
}
