<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Telescope\Http\Controllers;

use SwooleTW\Hyperf\Http\Request;
use SwooleTW\Hyperf\Telescope\Contracts\EntriesRepository;
use SwooleTW\Hyperf\Telescope\Storage\EntryQueryOptions;

use function SwooleTW\Hyperf\Cache\cache;
use function SwooleTW\Hyperf\Config\config;

abstract class EntryController
{
    /**
     * The entry type for the controller.
     */
    abstract protected function entryType(): string;

    /**
     * The watcher class for the controller.
     */
    abstract protected function watcher(): string;

    /**
     * List the entries of the given type.
     */
    public function index(Request $request, EntriesRepository $storage): array
    {
        return [
            'entries' => $storage->get(
                $this->entryType(),
                EntryQueryOptions::fromRequest($request)
            ),
            'status' => $this->status(),
        ];
    }

    /**
     * Get an entry with the given ID.
     */
    public function show(EntriesRepository $storage, string $id): array
    {
        $entry = $storage->find($id)->generateAvatar();

        return [
            'entry' => $entry,
            'batch' => $storage->get(null, EntryQueryOptions::forBatchId($entry->batchId)->limit(-1)),
        ];
    }

    /**
     * Determine the watcher recording status.
     */
    protected function status(): string
    {
        if (! config('telescope.enabled', false)) {
            return 'disabled';
        }

        if (cache('telescope:pause-recording', false)) {
            return 'paused';
        }

        $watcher = config('telescope.watchers.' . $this->watcher());

        if (! $watcher || (isset($watcher['enabled']) && ! $watcher['enabled'])) {
            return 'off';
        }

        return 'enabled';
    }
}
