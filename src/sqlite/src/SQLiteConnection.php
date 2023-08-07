<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Database;

use Doctrine\DBAL\Driver\PDO\SQLite\Driver as DoctrineDriver;
use Hyperf\Database\Connection;
use Hyperf\Database\Query\Grammars\Grammar as HyperfQueryGrammar;
use Hyperf\Database\Query\Processors\Processor;
use Hyperf\Database\Schema\Builder as SchemaBuilder;
use Hyperf\Database\Schema\Grammars\Grammar as HyperfSchemaGrammar;
use SwooleTW\Hyperf\Database\Query\Grammars\SQLiteGrammar as QueryGrammar;
use SwooleTW\Hyperf\Database\Query\Processors\SQLiteProcessor;
use SwooleTW\Hyperf\Database\Schema\Grammars\SQLiteGrammar as SchemaGrammar;
use SwooleTW\Hyperf\Database\Schema\SQLiteBuilder;

class SQLiteConnection extends Connection
{
    /**
     * Create a new database connection instance.
     *
     * @param  \PDO|\Closure  $pdo
     * @param  string  $database
     * @param  string  $tablePrefix
     * @param  array  $config
     * @return void
     */
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);

        $enableForeignKeyConstraints = $this->getForeignKeyConstraintsConfigurationValue();

        if ($enableForeignKeyConstraints === null) {
            return;
        }

        $enableForeignKeyConstraints
            ? $this->getSchemaBuilder()->enableForeignKeyConstraints()
            : $this->getSchemaBuilder()->disableForeignKeyConstraints();
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \SwooleTW\Hyperf\Database\Query\Grammars\SQLiteGrammar
     */
    protected function getDefaultQueryGrammar(): HyperfQueryGrammar
    {
        return $this->withTablePrefix(new QueryGrammar());
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \SwooleTW\Hyperf\Database\Schema\SQLiteBuilder
     */
    public function getSchemaBuilder(): SchemaBuilder
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new SQLiteBuilder($this);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \SwooleTW\Hyperf\Database\Schema\Grammars\SQLiteGrammar
     */
    protected function getDefaultSchemaGrammar(): HyperfSchemaGrammar
    {
        return $this->withTablePrefix(new SchemaGrammar());
    }

    /**
     * Get the default post processor instance.
     *
     * @return \SwooleTW\Hyperf\Database\Query\Processors\SQLiteProcessor
     */
    protected function getDefaultPostProcessor(): Processor
    {
        return new SQLiteProcessor;
    }

    /**
     * Get the Doctrine DBAL driver.
     *
     * @return \Doctrine\DBAL\Driver\PDOSqlite\Driver|\SwooleTW\Hyperf\Database\PDO\SQLiteDriver
     */
    protected function getDoctrineDriver()
    {
        return new DoctrineDriver;
    }

    /**
     * Get the database connection foreign key constraints configuration option.
     *
     * @return bool|null
     */
    protected function getForeignKeyConstraintsConfigurationValue()
    {
        return $this->getConfig('foreign_key_constraints');
    }
}
