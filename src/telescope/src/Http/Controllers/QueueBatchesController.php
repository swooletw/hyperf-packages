<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Telescope\Http\Controllers;

use Hyperf\Collection\Collection;
use SwooleTW\Hyperf\Bus\Contracts\BatchRepository;
use SwooleTW\Hyperf\Telescope\Contracts\EntriesRepository;
use SwooleTW\Hyperf\Telescope\EntryType;
use SwooleTW\Hyperf\Telescope\EntryUpdate;
use SwooleTW\Hyperf\Telescope\Storage\EntryQueryOptions;
use SwooleTW\Hyperf\Telescope\Watchers\BatchWatcher;

class QueueBatchesController extends EntryController
{
    /**
     * The entry type for the controller.
     */
    protected function entryType(): string
    {
        return EntryType::BATCH;
    }

    /**
     * The watcher class for the controller.
     */
    protected function watcher(): string
    {
        return BatchWatcher::class;
    }

    /**
     * Get an entry with the given ID.
     */
    public function show(EntriesRepository $storage, string $id): array
    {
        $batch = app(BatchRepository::class)->find($id);

        $storage->update(Collection::make([
            new EntryUpdate(
                (string) $id,
                EntryType::BATCH,
                $batch->toArray()
            ),
        ]));

        $entry = $storage->find($id)->generateAvatar();

        return [
            'entry' => $entry,
            'batch' => $storage->get(null, EntryQueryOptions::forBatchId($entry->batchId)->limit(-1)),
        ];
    }
}
