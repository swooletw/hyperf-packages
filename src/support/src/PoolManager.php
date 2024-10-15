<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support;

use Closure;
use InvalidArgumentException;
use SwooleTW\Hyperf\ObjectPool\PoolProxy;

abstract class PoolManager extends Manager
{
    /**
     * The array of drivers which will be wrapped as pool proxies.
     */
    protected array $poolables = [];

    /**
     * The array of release callbacks for the drivers.
     */
    protected array $releaseCallbacks = [];

    /**
     * Create a new driver instance.
     *
     * @throws InvalidArgumentException
     */
    protected function createDriver(string $driver): mixed
    {
        $instance = parent::createDriver($driver);

        if (! in_array($driver, $this->poolables)) {
            return $instance;
        }

        return new PoolProxy(
            static::class . ':' . $driver,
            fn () => $instance,
            $this->getPoolConfig($driver),
            $this->getReleaseCallback($driver)
        );
    }

    /**
     * Set the release callback for the driver.
     */
    public function setReleaseCallback(string $driver, Closure $callback): static
    {
        $this->releaseCallbacks[$driver] = $callback;

        return $this;
    }

    /**
     * Get the release callback for the driver.
     */
    public function getReleaseCallback(string $driver): ?Closure
    {
        return $this->releaseCallbacks[$driver] ?? null;
    }

    /**
     * Get the pool configuration for the driver.
     */
    abstract public function getPoolConfig(string $driver): array;

    /**
     * Add a driver to the poolables list.
     */
    public function addPoolable(string $driver): static
    {
        if (! in_array($driver, $this->poolables)) {
            $this->poolables[] = $driver;
        }

        return $this;
    }

    /**
     * Remove a driver from the poolables list.
     */
    public function removePoolable(string $driver): static
    {
        $index = array_search($driver, $this->poolables);
        if ($index === false) {
            return $this;
        }

        unset($this->poolables[$index]);

        return $this;
    }

    /**
     * Get the poolables list.
     */
    public function getPoolables(): array
    {
        return $this->poolables;
    }

    /**
     * Set the poolables list.
     */
    public function setPoolables(array $poolables): static
    {
        $this->poolables = $poolables;

        return $this;
    }
}
