<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use SwooleTW\Hyperf\Cache\Contracts\Store;

class TaggedCache extends Repository
{
    use RetrievesMultipleKeys {
        putMany as putManyAlias;
    }

    /**
     * The tag set instance.
     *
     * @var \SwooleTW\Hyperf\Cache\TagSet
     */
    protected $tags;

    /**
     * Create a new tagged cache instance.
     *
     * @param \SwooleTW\Hyperf\Cache\TagSet $tags
     */
    public function __construct(Store $store, TagSet $tags)
    {
        parent::__construct($store);

        $this->tags = $tags;
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
     *
     * @param null|int $ttl
     * @return bool
     */
    public function putMany(array $values, $ttl = null)
    {
        if ($ttl === null) {
            return $this->putManyForever($values);
        }

        return $this->putManyAlias($values, $ttl);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return bool|int
     */
    public function increment($key, $value = 1)
    {
        return $this->store->increment($this->itemKey($key), $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return bool|int
     */
    public function decrement($key, $value = 1)
    {
        return $this->store->decrement($this->itemKey($key), $value);
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        $this->tags->reset();

        return true;
    }

    /**
     * Get a fully qualified key for a tagged item.
     *
     * @param string $key
     * @return string
     */
    public function taggedItemKey($key)
    {
        return sha1($this->tags->getNamespace()) . ':' . $key;
    }

    /**
     * Get the tag set instance.
     *
     * @return \SwooleTW\Hyperf\Cache\TagSet
     */
    public function getTags()
    {
        return $this->tags;
    }

    protected function itemKey($key)
    {
        return $this->taggedItemKey($key);
    }

    /**
     * Fire an event for this cache instance.
     *
     * @param string $event
     */
    protected function event($event)
    {
        parent::event($event->setTags($this->tags->getNames()));
    }
}
