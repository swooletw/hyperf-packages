<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Testing;

use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\DbConnection\Db;
use SwooleTW\Hyperf\Foundation\Testing\RefreshDatabaseState;
use SwooleTW\Hyperf\Foundation\Testing\Traits\CanConfigureMigrationCommands;

trait RefreshDatabase
{
    use CanConfigureMigrationCommands;

    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function refreshDatabase(): void
    {
        $this->usingInMemoryDatabase()
            ? $this->refreshInMemoryDatabase()
            : $this->refreshTestDatabase();

        $this->afterRefreshingDatabase();
    }

    /**
     * Refresh the in-memory database.
     *
     * @return void
     */
    protected function refreshInMemoryDatabase(): void
    {
        // reset connection in connection pool in Hyperf\DbConnection\ConnectionResolver
        // data will be cleared once in-memory connection closed
        if ($connection = Context::get("database.connection.{$this->getRefreshConnection()}")) {
            $connection->reconnect();
        }

        $this->command('migrate', $this->migrateUsing());
    }

    /**
     * Determine if an in-memory database is being used.
     *
     * @return bool
     */
    protected function usingInMemoryDatabase(): bool
    {
        $config = $this->app->get(ConfigInterface::class);

        return $config->get("databases.{$this->getRefreshConnection()}.database") === ':memory:';
    }

    /**
     * The parameters that should be used when running "migrate".
     *
     * @return array
     */
    protected function migrateUsing(): array
    {
        return [
            '--seed' => $this->shouldSeed(),
            '--database' => $this->getRefreshConnection(),
        ];
    }

    /**
     * Refresh a conventional test database.
     *
     * @return void
     */
    protected function refreshTestDatabase(): void
    {
        if (! RefreshDatabaseState::$migrated) {
            $this->command('migrate:fresh', $this->migrateFreshUsing());

            RefreshDatabaseState::$migrated = true;
        }

        $this->beginDatabaseTransaction();
    }

    /**
     * Begin a database transaction on the testing database.
     *
     * @return void
     */
    public function beginDatabaseTransaction(): void
    {
        $database = $this->app->get(Db::class);

        foreach ($this->connectionsToTransact() as $name) {
            $connection = $database->connection($name);
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
                $connection->rollBack();
                $connection->setEventDispatcher($dispatcher);
                // this will trigger a database refresh warning
                // $connection->disconnect();
            }
        });
    }

    /**
     * The database connections that should have transactions.
     *
     * @return array
     */
    protected function connectionsToTransact(): array
    {
        return property_exists($this, 'connectionsToTransact')
            ? $this->connectionsToTransact : [null];
    }

    /**
     * Perform any work that should take place once the database has finished refreshing.
     *
     * @return void
     */
    protected function afterRefreshingDatabase(): void
    {
        // ...
    }

    protected function getRefreshConnection(): string
    {
        return $this->app
            ->get(ConfigInterface::class)
            ->get('databases.connection', 'default');
    }
}
