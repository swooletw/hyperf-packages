<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Database\Schema;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Schema\Builder;
use Hyperf\Support\Filesystem\Filesystem;

class SQLiteBuilder extends Builder
{
    /**
     * Create a database in the schema.
     *
     * @param  string  $name
     * @return bool
     */
    public function createDatabase($name): bool
    {
        return (bool) $this->getFilesystem()
            ->put($name, '');
    }

    /**
     * Drop a database from the schema if the database exists.
     *
     * @param  string  $name
     * @return bool
     */
    public function dropDatabaseIfExists($name): bool
    {
        $file = $this->getFilesystem();

        return $file->exists($name)
            ? $file->delete($name)
            : true;
    }

    /**
     * Drop all tables from the database.
     *
     * @return void
     */
    public function dropAllTables(): void
    {
        if ($this->connection->getDatabaseName() !== ':memory:') {
            $this->refreshDatabaseFile();
        }

        $this->connection->select($this->grammar->compileEnableWriteableSchema());

        $this->connection->select($this->grammar->compileDropAllTables());

        $this->connection->select($this->grammar->compileDisableWriteableSchema());

        $this->connection->select($this->grammar->compileRebuild());
    }

    /**
     * Drop all views from the database.
     *
     * @return void
     */
    public function dropAllViews(): void
    {
        $this->connection->select($this->grammar->compileEnableWriteableSchema());

        $this->connection->select($this->grammar->compileDropAllViews());

        $this->connection->select($this->grammar->compileDisableWriteableSchema());

        $this->connection->select($this->grammar->compileRebuild());
    }

    /**
     * Empty the database file.
     *
     * @return void
     */
    public function refreshDatabaseFile(): void
    {
        $this->getFilesystem()
            ->put($this->connection->getDatabaseName(), '');
    }

    protected function getFilesystem(): Filesystem
    {
        return ApplicationContext::getContainer()
            ->get(Filesystem::class);
    }
}
