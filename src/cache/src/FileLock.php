<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

class FileLock extends CacheLock
{
    /**
     * Attempt to acquire the lock.
     */
    public function acquire(): bool
    {
        return $this->store->add($this->name, $this->owner, $this->seconds);
    }
}
