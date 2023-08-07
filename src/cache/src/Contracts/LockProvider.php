<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache\Contracts;

interface LockProvider
{
    /**
     * Get a lock instance.
     *
     * @param string $name
     * @param int $seconds
     * @param null|string $owner
     * @return \SwooleTW\Hyperf\Cache\Contracts\Lock
     */
    public function lock($name, $seconds = 0, $owner = null);

    /**
     * Restore a lock instance using the owner identifier.
     *
     * @param string $name
     * @param string $owner
     * @return \SwooleTW\Hyperf\Cache\Contracts\Lock
     */
    public function restoreLock($name, $owner);
}
