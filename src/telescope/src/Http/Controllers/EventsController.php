<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Telescope\Http\Controllers;

use SwooleTW\Hyperf\Telescope\EntryType;
use SwooleTW\Hyperf\Telescope\Watchers\EventWatcher;

class EventsController extends EntryController
{
    /**
     * The entry type for the controller.
     */
    protected function entryType(): string
    {
        return EntryType::EVENT;
    }

    /**
     * The watcher class for the controller.
     */
    protected function watcher(): string
    {
        return EventWatcher::class;
    }
}
