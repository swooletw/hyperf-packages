<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Telescope\Http\Controllers;

use SwooleTW\Hyperf\Telescope\EntryType;
use SwooleTW\Hyperf\Telescope\Watchers\CacheWatcher;

class CacheController extends EntryController
{
    /**
     * The entry type for the controller.
     */
    protected function entryType(): string
    {
        return EntryType::CACHE;
    }

    /**
     * The watcher class for the controller.
     */
    protected function watcher(): string
    {
        return CacheWatcher::class;
    }
}
