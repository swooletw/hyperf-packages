<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Testing;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Connection as DatabaseConnection;
use Hyperf\DbConnection\Db;
use SwooleTW\Hyperf\Foundation\Testing\Traits\CanConfigureMigrationCommands;

trait RefreshDatabase
{
    use CanConfigureMigrationCommands;

    /**
     * Define hooks to migrate the database before and after each test.
     */
    public function refreshDatabase(): void
    {
        $this->beforeRefreshingDatabase();

        if ($this->usingInMemoryDatabase()) {
            $this->restoreInMemoryDatabase();
        }

        $this->refreshTestDatabase();

        $this->afterRefreshingDatabase();
    }

    /**
     * Restore the in-memory database between tests.
     */
    protected function restoreInMemoryDatabase(): void
    {
        $database = $this->app->get(Db::class);

        foreach ($this->connectionsToTransact() as $name) {
            if (isset(RefreshDatabaseState::$inMemoryConnections[$name])) {
                $database->connection($name)->setPdo(RefreshDatabaseState::$inMemoryConnections[$name]);
            }
        }
    }

    /**
     * Determine if an in-memory database is being used.
     */
    protected function usingInMemoryDatabase(): bool
    {
        $config = $this->app->get(ConfigInterface::class);

        return $config->get("database.connections.{$this->getRefreshConnection()}.database") === ':memory:';
    }

    /**
     * Refresh a conventional test database.
     */
    protected function refreshTestDatabase(): void
    {
        $migrateRefresh = property_exists($this, 'migrateRefresh') && (bool) $this->migrateRefresh;
        if ($migrateRefresh || ! RefreshDatabaseState::$migrated) {
            $this->command('migrate:fresh', $this->migrateFreshUsing());
            RefreshDatabaseState::$migrated = true;
            if ($migrateRefresh) {
                $this->migrateRefresh = false;
            }
        }

        $this->beginDatabaseTransaction();
    }

    /**
     * Begin a database transaction on the testing database.
     */
    public function beginDatabaseTransaction(): void
    {
        $database = $this->app->get(Db::class);

        foreach ($this->connectionsToTransact() as $name) {
            $connection = $database->connection($name);

            if ($this->usingInMemoryDatabase()) {
                RefreshDatabaseState::$inMemoryConnections[$name] ??= $connection->getPdo();
            }

            $dispatcher = $connection->getEventDispatcher();

            $connection->unsetEventDispatcher();
            $connection->beginTransaction();
            $connection->setEventDispatcher($dispatcher);
        }

        $this->beforeApplicationDestroyed(function () use ($database) {
            foreach ($this->connectionsToTransact() as $name) {
                $connection = $database->connection($name);
                $dispatcher = $connection->getEventDispatcher();

                $connection->unsetEventDispatcher();

                if (! $connection->getPdo()->inTransaction()) {
                    RefreshDatabaseState::$migrated = false;
                }

                if ($connection instanceof DatabaseConnection) {
                    $connection->resetRecordsModified();
                }

                $connection->rollBack();
                $connection->setEventDispatcher($dispatcher);
                // this will trigger a database refresh warning
                // $connection->disconnect();
            }
        });
    }

    /**
     * The database connections that should have transactions.
     */
    protected function connectionsToTransact(): array
    {
        return property_exists($this, 'connectionsToTransact')
            ? $this->connectionsToTransact : [null];
    }

    /**
     * Perform any work that should take place before the database has started refreshing.
     */
    protected function beforeRefreshingDatabase(): void
    {
        // ...
    }

    /**
     * Perform any work that should take place once the database has finished refreshing.
     */
    protected function afterRefreshingDatabase(): void
    {
        // ...
    }

    protected function getRefreshConnection(): string
    {
        return $this->app
            ->get(ConfigInterface::class)
            ->get('database.default');
    }
}
