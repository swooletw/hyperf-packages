<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

trait HasCacheLock
{
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
        return new CacheLock($this, $name, $seconds, $owner);
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
}
