<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use SwooleTW\Hyperf\Cache\Contracts\Store;

abstract class TaggableStore implements Store
{
    /**
     * Begin executing a new tags operation.
     *
     * @param array|mixed $names
     * @return \SwooleTW\Hyperf\Cache\TaggedCache
     */
    public function tags($names)
    {
        return new TaggedCache($this, new TagSet($this, is_array($names) ? $names : func_get_args()));
    }
}
