<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use DateInterval;
use DateTimeInterface;

class RedisTaggedCache extends TaggedCache
{
    /**
     * Store an item in the cache if the key does not exist.
     */
    public function add(string $key, mixed $value, null|DateInterval|DateTimeInterface|int $ttl = null): bool
    {
        $this->tags->addEntry(
            $this->itemKey($key),
            ! is_null($ttl) ? $this->getSeconds($ttl) : 0
        );

        return parent::add($key, $value, $ttl);
    }

    /**
     * Store an item in the cache.
     */
    public function put(string $key, mixed $value, null|DateInterval|DateTimeInterface|int $ttl = null): bool
    {
        if (is_null($ttl)) {
            return $this->forever($key, $value);
        }

        $this->tags->addEntry(
            $this->itemKey($key),
            $this->getSeconds($ttl)
        );

        return parent::put($key, $value, $ttl);
    }

    /**
     * Increment the value of an item in the cache.
     */
    public function increment(string $key, int $value = 1): bool|int
    {
        $this->tags->addEntry($this->itemKey($key), updateWhen: 'NX');

        return parent::increment($key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     */
    public function decrement(string $key, int $value = 1): bool|int
    {
        $this->tags->addEntry($this->itemKey($key), updateWhen: 'NX');

        return parent::decrement($key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     */
    public function forever(string $key, mixed $value): bool
    {
        $this->tags->addEntry($this->itemKey($key));

        return parent::forever($key, $value);
    }

    /**
     * Remove all items from the cache.
     */
    public function flush(): true
    {
        $this->flushValues();
        $this->tags->flush();

        return true;
    }

    /**
     * Flush the individual cache entries for the tags.
     */
    protected function flushValues(): void
    {
        $entries = $this->tags->entries()
            ->map(fn (string $key) => $this->store->getPrefix() . $key)
            ->chunk(1000);

        foreach ($entries as $cacheKeys) {
            $this->store->connection()->del(...$cacheKeys);
        }
    }

    /**
     * Remove all stale reference entries from the tag set.
     */
    public function flushStale(): true
    {
        $this->tags->flushStaleEntries();

        return true;
    }
}
