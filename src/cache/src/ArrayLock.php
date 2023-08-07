<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use Carbon\Carbon;

class ArrayLock extends Lock
{
    /**
     * The parent array cache store.
     *
     * @var \SwooleTW\Hyperf\Cache\ArrayStore
     */
    protected $store;

    /**
     * Create a new lock instance.
     *
     * @param \SwooleTW\Hyperf\Cache\ArrayStore $store
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
        $expiration = $this->store->locks[$this->name]['expiresAt'] ?? Carbon::now()->addSecond();

        if ($this->exists() && $expiration->isFuture()) {
            return false;
        }

        $this->store->locks[$this->name] = [
            'owner' => $this->owner,
            'expiresAt' => $this->seconds === 0 ? null : Carbon::now()->addSeconds($this->seconds),
        ];

        return true;
    }

    /**
     * Release the lock.
     *
     * @return bool
     */
    public function release()
    {
        if (! $this->exists()) {
            return false;
        }

        if (! $this->isOwnedByCurrentProcess()) {
            return false;
        }

        $this->forceRelease();

        return true;
    }

    /**
     * Releases this lock in disregard of ownership.
     */
    public function forceRelease()
    {
        unset($this->store->locks[$this->name]);
    }

    /**
     * Determine if the current lock exists.
     *
     * @return bool
     */
    protected function exists()
    {
        return isset($this->store->locks[$this->name]);
    }

    /**
     * Returns the owner value written into the driver for this lock.
     *
     * @return string
     */
    protected function getCurrentOwner()
    {
        return $this->store->locks[$this->name]['owner'];
    }
}
