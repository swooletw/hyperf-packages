<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

class NoLock extends Lock
{
    /**
     * Attempt to acquire the lock.
     */
    public function acquire(): true
    {
        return true;
    }

    /**
     * Release the lock.
     */
    public function release(): true
    {
        return true;
    }

    /**
     * Releases this lock in disregard of ownership.
     */
    public function forceRelease(): void {}

    /**
     * Returns the owner value written into the driver for this lock.
     */
    protected function getCurrentOwner(): string
    {
        return $this->owner;
    }
}
