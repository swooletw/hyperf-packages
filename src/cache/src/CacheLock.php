<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

class CacheLock extends Lock
{
    /**
     * The cache store implementation.
     *
     * @var \SwooleTW\Hyperf\Cache\Contracts\Store
     */
    protected $store;

    /**
     * Create a new lock instance.
     *
     * @param \SwooleTW\Hyperf\Cache\Contracts\Store $store
     * @param string $name
     * @param int $seconds
     * @param null|string $owner
     */
    public function __construct($store, $name, $seconds, $owner = null)
    {
        parent::__construct($name, $seconds, $owner);

        $this->store = $store;
    }

    /**
     * Attempt to acquire the lock.
     *
     * @return bool
     */
    public function acquire()
    {
        if (method_exists($this->store, 'add') && $this->seconds > 0) {
            return $this->store->add(
                $this->name,
                $this->owner,
                $this->seconds
            );
        }

        if (! is_null($this->store->get($this->name))) {
            return false;
        }

        return ($this->seconds > 0)
                ? $this->store->put($this->name, $this->owner, $this->seconds)
                : $this->store->forever($this->name, $this->owner, $this->seconds);
    }

    /**
     * Release the lock.
     *
     * @return bool
     */
    public function release()
    {
        if ($this->isOwnedByCurrentProcess()) {
            return $this->store->forget($this->name);
        }

        return false;
    }

    /**
     * Releases this lock regardless of ownership.
     */
    public function forceRelease()
    {
        $this->store->forget($this->name);
    }

    /**
     * Returns the owner value written into the driver for this lock.
     *
     * @return mixed
     */
    protected function getCurrentOwner()
    {
        return $this->store->get($this->name);
    }
}
