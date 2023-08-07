<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

class NoLock extends Lock
{
    /**
     * Attempt to acquire the lock.
     *
     * @return bool
     */
    public function acquire()
    {
        return true;
    }

    /**
     * Release the lock.
     *
     * @return bool
     */
    public function release()
    {
        return true;
    }

    /**
     * Releases this lock in disregard of ownership.
     */
    public function forceRelease()
    {
    }

    /**
     * Returns the owner value written into the driver for this lock.
     *
     * @return mixed
     */
    protected function getCurrentOwner()
    {
        return $this->owner;
    }
}
