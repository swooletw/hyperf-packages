<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use Hyperf\Collection\Collection;

class RedisTagSet extends TagSet
{
    /**
     * Add a reference entry to the tag set's underlying sorted set.
     */
    public function addEntry(string $key, int $ttl = 0, string $updateWhen = null): void
    {
        $ttl = $ttl > 0 ? now()->addSeconds($ttl)->getTimestamp() : -1;

        foreach ($this->tagIds() as $tagKey) {
            if ($updateWhen) {
                $this->store->connection()->zadd($this->store->getPrefix() . $tagKey, $updateWhen, $ttl, $key);
            } else {
                $this->store->connection()->zadd($this->store->getPrefix() . $tagKey, $ttl, $key);
            }
        }
    }

    /**
     * Get all of the cache entry keys for the tag set.
     */
    public function entries(): Collection
    {
        // TODO: Lazy Collection
        $collection = collect();

        foreach ($this->tagIds() as $tagKey) {
            $cursor = $defaultCursorValue = '0';

            do {
                [$cursor, $entries] = $this->store->connection()->zscan(
                    $this->store->getPrefix() . $tagKey,
                    $cursor,
                    ['match' => '*', 'count' => 1000]
                );

                if (! is_array($entries)) {
                    break;
                }

                $entries = array_unique(array_keys($entries));

                if (count($entries) === 0) {
                    continue;
                }

                foreach ($entries as $entry) {
                    $collection[] = $entry;
                }
            } while (((string) $cursor) !== $defaultCursorValue);
        }

        return $collection;
    }

    /**
     * Remove the stale entries from the tag set.
     */
    public function flushStaleEntries(): void
    {
        $this->store->connection()->pipeline(function ($pipe) {
            foreach ($this->tagIds() as $tagKey) {
                $pipe->zremrangebyscore($this->store->getPrefix() . $tagKey, 0, now()->getTimestamp());
            }
        });
    }

    /**
     * Flush the tag from the cache.
     */
    public function flushTag(string $name): string
    {
        return $this->resetTag($name);
    }

    /**
     * Reset the tag and return the new tag identifier.
     */
    public function resetTag(string $name): string
    {
        $this->store->forget($this->tagKey($name));

        return $this->tagId($name);
    }

    /**
     * Get the unique tag identifier for a given tag.
     */
    public function tagId(string $name): string
    {
        return "tag:{$name}:entries";
    }

    /**
     * Get the tag identifier key for a given tag.
     */
    public function tagKey(string $name): string
    {
        return "tag:{$name}:entries";
    }
}
