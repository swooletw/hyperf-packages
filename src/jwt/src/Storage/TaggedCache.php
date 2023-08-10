<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT\Storage;

use SwooleTW\Hyperf\JWT\Contracts\StorageContract;
use SwooleTW\Hyperf\Cache\Contracts\Repository as CacheContract;

class TaggedCache implements StorageContract
{
    protected string $tag = 'jwt_blacklist';

    /**
     * Constructor.
     *
     * @param  \SwooleTW\Hyperf\Cache\Contracts\Repository  $cache
     * @return void
     */
    public function __construct(
        protected CacheContract $cache
    ) {}

    /**
     * Add a new item into storage.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $minutes
     * @return void
     */
    public function add(string $key, mixed $value, int $minutes): void
    {
        $this->cache->tags([$this->tag])->put($key, $value, $minutes * 60);
    }

    /**
     * Add a new item into storage forever.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function forever(string $key, mixed $value): void
    {
        $this->cache->tags([$this->tag])->forever($key, $value);
    }

    /**
     * Get an item from storage.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $this->cache->tags([$this->tag])->get($key);
    }

    /**
     * Remove an item from storage.
     *
     * @param  string  $key
     * @return bool
     */
    public function destroy(string $key): bool
    {
        return $this->cache->tags([$this->tag])->forget($key);
    }

    /**
     * Remove all items associated with the tag.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->cache->tags([$this->tag])->flush();
    }
}