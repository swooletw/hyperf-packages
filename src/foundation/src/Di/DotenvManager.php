<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Di;

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;

class DotenvManager
{
    protected static PutenvAdapter $adapter;

    protected static Dotenv $dotenv;

    protected static array $entries;

    public static function init(): void
    {
        static::$adapter = PutenvAdapter::create()->get();

        $repository = RepositoryBuilder::createWithNoAdapters()->addAdapter(static::$adapter)->make();

        static::$dotenv = Dotenv::create($repository, [BASE_PATH]);
    }

    public static function load(): void
    {
        if (isset(static::$entries)) {
            return;
        }

        static::$entries = static::$dotenv->load();
    }

    public static function reload(): void
    {
        if (! isset(static::$entries)) {
            static::load();

            return;
        }

        $entries = static::$dotenv->load();
        $deletedEntries = array_diff_key(static::$entries, $entries);

        foreach ($deletedEntries as $deletedEntry => $_) {
            static::$adapter->delete($deletedEntry);
        }

        static::$entries = $entries;
    }
}
