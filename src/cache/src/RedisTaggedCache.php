<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use DateInterval;
use DateTimeInterface;

class RedisTaggedCache extends TaggedCache
{
    /**
     * Forever reference key.
     */
    public const REFERENCE_KEY_FOREVER = 'forever_ref';

    /**
     * Standard reference key.
     */
    public const REFERENCE_KEY_STANDARD = 'standard_ref';

    /**
     * Store an item in the cache.
     */
    public function put(string $key, mixed $value, null|DateInterval|DateTimeInterface|int $ttl = null): bool
    {
        if ($ttl === null) {
            return $this->forever($key, $value);
        }

        $this->pushStandardKeys($this->tags->getNamespace(), $key);

        return parent::put($key, $value, $ttl);
    }

    /**
     * Increment the value of an item in the cache.
     */
    public function increment(string $key, int $value = 1): bool|int
    {
        $this->pushStandardKeys($this->tags->getNamespace(), $key);

        return parent::increment($key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     */
    public function decrement(string $key, int $value = 1): bool|int
    {
        $this->pushStandardKeys($this->tags->getNamespace(), $key);

        return parent::decrement($key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     */
    public function forever(string $key, mixed $value): bool
    {
        $this->pushForeverKeys($this->tags->getNamespace(), $key);

        return parent::forever($key, $value);
    }

    /**
     * Remove all items from the cache.
     */
    public function flush(): true
    {
        $this->deleteForeverKeys();
        $this->deleteStandardKeys();

        return parent::flush();
    }

    /**
     * Store standard key references into store.
     */
    protected function pushStandardKeys(string $namespace, string $key): void
    {
        $this->pushKeys($namespace, $key, self::REFERENCE_KEY_STANDARD);
    }

    /**
     * Store forever key references into store.
     */
    protected function pushForeverKeys(string $namespace, string $key): void
    {
        $this->pushKeys($namespace, $key, self::REFERENCE_KEY_FOREVER);
    }

    /**
     * Store a reference to the cache key against the reference key.
     */
    protected function pushKeys(string $namespace, string $key, string $reference): void
    {
        $fullKey = $this->store->getPrefix() . sha1($namespace) . ':' . $key;

        foreach (explode('|', $namespace) as $segment) {
            $this->store->connection()->sadd($this->referenceKey($segment, $reference), $fullKey);
        }
    }

    /**
     * Delete all of the items that were stored forever.
     */
    protected function deleteForeverKeys()
    {
        $this->deleteKeysByReference(self::REFERENCE_KEY_FOREVER);
    }

    /**
     * Delete all standard items.
     */
    protected function deleteStandardKeys()
    {
        $this->deleteKeysByReference(self::REFERENCE_KEY_STANDARD);
    }

    /**
     * Find and delete all of the items that were stored against a reference.
     */
    protected function deleteKeysByReference(string $reference): void
    {
        foreach (explode('|', $this->tags->getNamespace()) as $segment) {
            $this->deleteValues($segment = $this->referenceKey($segment, $reference));

            $this->store->connection()->del($segment);
        }
    }

    /**
     * Delete item keys that have been stored against a reference.
     */
    protected function deleteValues(string $referenceKey): void
    {
        $values = array_unique($this->store->connection()->smembers($referenceKey));

        if (count($values) > 0) {
            foreach (array_chunk($values, 1000) as $valuesChunk) {
                $this->store->connection()->del(...$valuesChunk);
            }
        }
    }

    /**
     * Get the reference key for the segment.
     */
    protected function referenceKey(string $segment, string $suffix): string
    {
        return $this->store->getPrefix() . $segment . ':' . $suffix;
    }
}
