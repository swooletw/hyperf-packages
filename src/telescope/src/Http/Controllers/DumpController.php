<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Telescope\Http\Controllers;

use SwooleTW\Hyperf\Cache\ArrayStore;
use SwooleTW\Hyperf\Cache\Contracts\Factory as CacheFactory;
use SwooleTW\Hyperf\Http\Request;
use SwooleTW\Hyperf\Telescope\Contracts\EntriesRepository;
use SwooleTW\Hyperf\Telescope\EntryType;
use SwooleTW\Hyperf\Telescope\Storage\EntryQueryOptions;
use SwooleTW\Hyperf\Telescope\Watchers\DumpWatcher;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class DumpController extends EntryController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CacheFactory $cache
    ) {
    }

    /**
     * List the entries of the given type.
     */
    public function index(Request $request, EntriesRepository $storage): array
    {
        /* @phpstan-ignore-next-line */
        $this->cache->put('telescope:dump-watcher', true, now()->addSeconds(4));

        return [
            'dump' => (new HtmlDumper())->dump((new VarCloner())->cloneVar(true), true),
            'entries' => $storage->get(
                $this->entryType(),
                EntryQueryOptions::fromRequest($request)
            ),
            'status' => $this->status(),
        ];
    }

    /**
     * Determine the watcher recording status.
     */
    protected function status(): string
    {
        /* @phpstan-ignore-next-line */
        if ($this->cache->getStore() instanceof ArrayStore) {
            return 'wrong-cache';
        }

        return parent::status();
    }

    /**
     * The entry type for the controller.
     */
    protected function entryType(): string
    {
        return EntryType::DUMP;
    }

    /**
     * The watcher class for the controller.
     */
    protected function watcher(): string
    {
        return DumpWatcher::class;
    }
}
