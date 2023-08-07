<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use Hyperf\Redis\Redis;

class RedisLock extends Lock
{
    /**
     * The Redis factory implementation.
     *
     * @var Redis
     */
    protected $redis;

    /**
     * Create a new lock instance.
     *
     * @param Redis $redis
     * @param string $name
     * @param int $seconds
     * @param null|string $owner
     */
    public function __construct($redis, $name, $seconds, $owner = null)
    {
        parent::__construct($name, $seconds, $owner);

        $this->redis = $redis;
    }

    /**
     * Attempt to acquire the lock.
     *
     * @return bool
     */
    public function acquire()
    {
        if ($this->seconds > 0) {
            return $this->redis->set($this->name, $this->owner, ['EX' => $this->seconds, 'NX']) == true;
        }
        return $this->redis->setnx($this->name, $this->owner) == true;
    }

    /**
     * Release the lock.
     *
     * @return bool
     */
    public function release()
    {
        return (bool) $this->redis->eval(LuaScripts::releaseLock(), [$this->name, $this->owner], 1);
    }

    /**
     * Releases this lock in disregard of ownership.
     */
    public function forceRelease()
    {
        $this->redis->del($this->name);
    }

    /**
     * Returns the owner value written into the driver for this lock.
     *
     * @return string
     */
    protected function getCurrentOwner()
    {
        return $this->redis->get($this->name);
    }
}
