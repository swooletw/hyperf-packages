<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT\Storage;

use SwooleTW\Hyperf\Cache\Contracts\Repository as CacheContract;
use SwooleTW\Hyperf\JWT\Contracts\StorageContract;

class TaggedCache implements StorageContract
{
    protected string $tag = 'jwt_blacklist';

    /**
     * Constructor.
     */
    public function __construct(
        protected CacheContract $cache
    ) {}

    /**
     * Add a new item into storage.
     */
    public function add(string $key, mixed $value, int $minutes): void
    {
        $this->cache->tags([$this->tag])->put($key, $value, $minutes * 60);
    }

    /**
     * Add a new item into storage forever.
     */
    public function forever(string $key, mixed $value): void
    {
        $this->cache->tags([$this->tag])->forever($key, $value);
    }

    /**
     * Get an item from storage.
     */
    public function get(string $key): mixed
    {
        return $this->cache->tags([$this->tag])->get($key);
    }

    /**
     * Remove an item from storage.
     */
    public function destroy(string $key): bool
    {
        return $this->cache->tags([$this->tag])->forget($key);
    }

    /**
     * Remove all items associated with the tag.
     */
    public function flush(): void
    {
        $this->cache->tags([$this->tag])->flush();
    }
}
