<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Database\Schema\Grammars;

use Doctrine\DBAL\Schema\Index;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Database\Connection;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Grammars\Grammar;
use Hyperf\Stringable\Str;
use Hyperf\Support\Fluent;
use RuntimeException;

class SQLiteGrammar extends Grammar
{
    /**
     * The possible column modifiers.
     *
     * @var string[]
     */
    protected $modifiers = ['VirtualAs', 'StoredAs', 'Nullable', 'Default', 'Increment'];

    /**
     * The columns available as serials.
     *
     * @var string[]
     */
    protected $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];

    /**
     * Compile the query to determine if a table exists.
     */
    public function compileTableExists(): string
    {
        return "select * from sqlite_master where type = 'table' and name = ?";
    }

    /**
     * Compile the query to determine the list of columns.
     *
     * @param string $table
     */
    public function compileColumnListing($table): string
    {
        return 'pragma table_info(' . $this->wrap(str_replace('.', '__', $table)) . ')';
    }

    /**
     * Compile a create table command.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $command
     */
    public function compileCreate(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf(
            '%s table %s (%s%s%s)',
            $blueprint->temporary ? 'create temporary' : 'create',
            $this->wrapTable($blueprint),
            implode(', ', $this->getColumns($blueprint)),
            (string) $this->addForeignKeys($blueprint),
            (string) $this->addPrimaryKeys($blueprint)
        );
    }

    /**
     * Get the foreign key syntax for a table creation statement.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     */
    protected function addForeignKeys(Blueprint $blueprint): ?string
    {
        $foreigns = $this->getCommandsByName($blueprint, 'foreign');

        return Collection::make($foreigns)->reduce(function ($sql, $foreign) {
            // Once we have all the foreign key commands for the table creation statement
            // we'll loop through each of them and add them to the create table SQL we
            // are building, since SQLite needs foreign keys on the tables creation.
            $sql .= $this->getForeignKey($foreign);

            if (! is_null($foreign->onDelete)) {
                $sql .= " on delete {$foreign->onDelete}";
            }

            // If this foreign key specifies the action to be taken on update we will add
            // that to the statement here. We'll append it to this SQL and then return
            // the SQL so we can keep adding any other foreign constraints onto this.
            if (! is_null($foreign->onUpdate)) {
                $sql .= " on update {$foreign->onUpdate}";
            }

            return $sql;
        }, '');

        return null;
    }

    /**
     * Get the SQL for the foreign key.
     *
     * @param \Hyperf\Utils\Fluent $foreign
     */
    protected function getForeignKey($foreign): string
    {
        // We need to columnize the columns that the foreign key is being defined for
        // so that it is a properly formatted list. Once we have done this, we can
        // return the foreign key SQL declaration to the calling method for use.
        return sprintf(
            ', foreign key(%s) references %s(%s)',
            $this->columnize($foreign->columns),
            $this->wrapTable($foreign->on),
            $this->columnize((array) $foreign->references)
        );
    }

    /**
     * Get the primary key syntax for a table creation statement.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     */
    protected function addPrimaryKeys(Blueprint $blueprint): ?string
    {
        if (! is_null($primary = $this->getCommandByName($blueprint, 'primary'))) {
            return ", primary key ({$this->columnize($primary->columns)})";
        }

        return null;
    }

    /**
     * Compile alter table commands for adding columns.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $command
     */
    public function compileAdd(Blueprint $blueprint, Fluent $command): array
    {
        $columns = $this->prefixArray('add column', $this->getColumns($blueprint));

        return Collection::make($columns)->reject(function ($column) {
            return preg_match('/as \(.*\) stored/', $column) > 0;
        })->map(function ($column) use ($blueprint) {
            return 'alter table ' . $this->wrapTable($blueprint) . ' ' . $column;
        })->all();
    }

    /**
     * Compile a unique key command.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $command
     */
    public function compileUnique(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf(
            'create unique index %s on %s (%s)',
            $this->wrap($command->index),
            $this->wrapTable($blueprint),
            $this->columnize($command->columns)
        );
    }

    /**
     * Compile a plain index key command.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $command
     */
    public function compileIndex(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf(
            'create index %s on %s (%s)',
            $this->wrap($command->index),
            $this->wrapTable($blueprint),
            $this->columnize($command->columns)
        );
    }

    /**
     * Compile a spatial index key command.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $command
     *
     * @throws RuntimeException
     */
    public function compileSpatialIndex(Blueprint $blueprint, Fluent $command): void
    {
        throw new RuntimeException('The database driver in use does not support spatial indexes.');
    }

    /**
     * Compile a foreign key command.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $command
     */
    public function compileForeign(Blueprint $blueprint, Fluent $command): string
    {
        // Handled on table creation...
        return '';
    }

    /**
     * Compile a drop table command.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $command
     */
    public function compileDrop(Blueprint $blueprint, Fluent $command): string
    {
        return 'drop table ' . $this->wrapTable($blueprint);
    }

    /**
     * Compile a drop table (if exists) command.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $command
     */
    public function compileDropIfExists(Blueprint $blueprint, Fluent $command): string
    {
        return 'drop table if exists ' . $this->wrapTable($blueprint);
    }

    /**
     * Compile the SQL needed to drop all tables.
     */
    public function compileDropAllTables(): string
    {
        return "delete from sqlite_master where type in ('table', 'index', 'trigger')";
    }

    /**
     * Compile the SQL needed to drop all views.
     */
    public function compileDropAllViews(): string
    {
        return "delete from sqlite_master where type in ('view')";
    }

    /**
     * Compile the SQL needed to rebuild the database.
     */
    public function compileRebuild(): string
    {
        return 'vacuum';
    }

    /**
     * Compile a drop column command.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $command
     */
    public function compileDropColumn(Blueprint $blueprint, Fluent $command, Connection $connection): array
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->prefixArray('drop column', $this->wrapArray($command->columns));

        return Collection::make($columns)
            ->map(
                fn ($column) => 'alter table ' . $table . ' ' . $column
            )->all();
    }

    /**
     * Compile a drop unique key command.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $command
     */
    public function compileDropUnique(Blueprint $blueprint, Fluent $command): string
    {
        $index = $this->wrap($command->index);

        return "drop index {$index}";
    }

    /**
     * Compile a drop index command.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $command
     */
    public function compileDropIndex(Blueprint $blueprint, Fluent $command): string
    {
        $index = $this->wrap($command->index);

        return "drop index {$index}";
    }

    /**
     * Compile a drop spatial index command.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $command
     *
     * @throws RuntimeException
     */
    public function compileDropSpatialIndex(Blueprint $blueprint, Fluent $command): void
    {
        throw new RuntimeException('The database driver in use does not support spatial indexes.');
    }

    /**
     * Compile a rename table command.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $command
     */
    public function compileRename(Blueprint $blueprint, Fluent $command): string
    {
        $from = $this->wrapTable($blueprint);

        return "alter table {$from} rename to " . $this->wrapTable($command->to);
    }

    /**
     * Compile a rename index command.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $command
     * @param \SwooleTW\Hyperf\Database\Connection $connection
     *
     * @throws RuntimeException
     */
    public function compileRenameIndex(Blueprint $blueprint, Fluent $command, Connection $connection): array
    {
        $schemaManager = $connection->getDoctrineSchemaManager();

        $indexes = $schemaManager->listTableIndexes($this->getTablePrefix() . $blueprint->getTable());

        $index = Arr::get($indexes, $command->from);

        if (! $index) {
            throw new RuntimeException("Index [{$command->from}] does not exist.");
        }

        $newIndex = new Index(
            $command->to,
            $index->getColumns(),
            $index->isUnique(),
            $index->isPrimary(),
            $index->getFlags(),
            $index->getOptions()
        );

        $platform = $schemaManager->getDatabasePlatform();

        return [
            $platform->getDropIndexSQL($command->from, $this->getTablePrefix() . $blueprint->getTable()),
            $platform->getCreateIndexSQL($newIndex, $this->getTablePrefix() . $blueprint->getTable()),
        ];
    }

    /**
     * Compile the command to enable foreign key constraints.
     */
    public function compileEnableForeignKeyConstraints(): string
    {
        return 'PRAGMA foreign_keys = ON;';
    }

    /**
     * Compile the command to disable foreign key constraints.
     */
    public function compileDisableForeignKeyConstraints(): string
    {
        return 'PRAGMA foreign_keys = OFF;';
    }

    /**
     * Compile the SQL needed to enable a writable schema.
     */
    public function compileEnableWriteableSchema(): string
    {
        return 'PRAGMA writable_schema = 1;';
    }

    /**
     * Compile the SQL needed to disable a writable schema.
     */
    public function compileDisableWriteableSchema(): string
    {
        return 'PRAGMA writable_schema = 0;';
    }

    /**
     * Create the column definition for a char type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeChar(Fluent $column): string
    {
        return 'varchar';
    }

    /**
     * Create the column definition for a string type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeString(Fluent $column): string
    {
        return 'varchar';
    }

    /**
     * Create the column definition for a tiny text type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeTinyText(Fluent $column): string
    {
        return 'text';
    }

    /**
     * Create the column definition for a text type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeText(Fluent $column): string
    {
        return 'text';
    }

    /**
     * Create the column definition for a medium text type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeMediumText(Fluent $column): string
    {
        return 'text';
    }

    /**
     * Create the column definition for a long text type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeLongText(Fluent $column): string
    {
        return 'text';
    }

    /**
     * Create the column definition for an integer type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeInteger(Fluent $column): string
    {
        return 'integer';
    }

    /**
     * Create the column definition for a big integer type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeBigInteger(Fluent $column): string
    {
        return 'integer';
    }

    /**
     * Create the column definition for a medium integer type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeMediumInteger(Fluent $column): string
    {
        return 'integer';
    }

    /**
     * Create the column definition for a tiny integer type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeTinyInteger(Fluent $column): string
    {
        return 'integer';
    }

    /**
     * Create the column definition for a small integer type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeSmallInteger(Fluent $column): string
    {
        return 'integer';
    }

    /**
     * Create the column definition for a float type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeFloat(Fluent $column): string
    {
        return 'float';
    }

    /**
     * Create the column definition for a double type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeDouble(Fluent $column): string
    {
        return 'float';
    }

    /**
     * Create the column definition for a decimal type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeDecimal(Fluent $column): string
    {
        return 'numeric';
    }

    /**
     * Create the column definition for a boolean type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeBoolean(Fluent $column): string
    {
        return 'tinyint(1)';
    }

    /**
     * Create the column definition for an enumeration type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeEnum(Fluent $column): string
    {
        return sprintf(
            'varchar check ("%s" in (%s))',
            $column->name,
            $this->quoteString($column->allowed)
        );
    }

    /**
     * Create the column definition for a json type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeJson(Fluent $column): string
    {
        return 'text';
    }

    /**
     * Create the column definition for a jsonb type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeJsonb(Fluent $column): string
    {
        return 'text';
    }

    /**
     * Create the column definition for a date type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeDate(Fluent $column): string
    {
        return 'date';
    }

    /**
     * Create the column definition for a date-time type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeDateTime(Fluent $column): string
    {
        return $this->typeTimestamp($column);
    }

    /**
     * Create the column definition for a date-time (with time zone) type.
     *
     * Note: "SQLite does not have a storage class set aside for storing dates and/or times."
     *
     * @link https://www.sqlite.org/datatype3.html
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeDateTimeTz(Fluent $column): string
    {
        return $this->typeDateTime($column);
    }

    /**
     * Create the column definition for a time type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeTime(Fluent $column): string
    {
        return 'time';
    }

    /**
     * Create the column definition for a time (with time zone) type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeTimeTz(Fluent $column): string
    {
        return $this->typeTime($column);
    }

    /**
     * Create the column definition for a timestamp type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeTimestamp(Fluent $column): string
    {
        return $column->useCurrent ? 'datetime default CURRENT_TIMESTAMP' : 'datetime';
    }

    /**
     * Create the column definition for a timestamp (with time zone) type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeTimestampTz(Fluent $column): string
    {
        return $this->typeTimestamp($column);
    }

    /**
     * Create the column definition for a year type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeYear(Fluent $column): string
    {
        return $this->typeInteger($column);
    }

    /**
     * Create the column definition for a binary type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeBinary(Fluent $column): string
    {
        return 'blob';
    }

    /**
     * Create the column definition for a uuid type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeUuid(Fluent $column): string
    {
        return 'varchar';
    }

    /**
     * Create the column definition for an IP address type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeIpAddress(Fluent $column): string
    {
        return 'varchar';
    }

    /**
     * Create the column definition for a MAC address type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function typeMacAddress(Fluent $column): string
    {
        return 'varchar';
    }

    /**
     * Create the column definition for a spatial Geometry type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    public function typeGeometry(Fluent $column): string
    {
        return 'geometry';
    }

    /**
     * Create the column definition for a spatial Point type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    public function typePoint(Fluent $column): string
    {
        return 'point';
    }

    /**
     * Create the column definition for a spatial LineString type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    public function typeLineString(Fluent $column): string
    {
        return 'linestring';
    }

    /**
     * Create the column definition for a spatial Polygon type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    public function typePolygon(Fluent $column): string
    {
        return 'polygon';
    }

    /**
     * Create the column definition for a spatial GeometryCollection type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    public function typeGeometryCollection(Fluent $column): string
    {
        return 'geometrycollection';
    }

    /**
     * Create the column definition for a spatial MultiPoint type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    public function typeMultiPoint(Fluent $column): string
    {
        return 'multipoint';
    }

    /**
     * Create the column definition for a spatial MultiLineString type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    public function typeMultiLineString(Fluent $column): string
    {
        return 'multilinestring';
    }

    /**
     * Create the column definition for a spatial MultiPolygon type.
     *
     * @param \Hyperf\Utils\Fluent $column
     */
    public function typeMultiPolygon(Fluent $column): string
    {
        return 'multipolygon';
    }

    /**
     * Create the column definition for a generated, computed column type.
     *
     * @param \Hyperf\Utils\Fluent $column
     *
     * @throws RuntimeException
     */
    protected function typeComputed(Fluent $column): void
    {
        throw new RuntimeException('This database driver requires a type, see the virtualAs / storedAs modifiers.');
    }

    /**
     * Get the SQL for a generated virtual column modifier.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function modifyVirtualAs(Blueprint $blueprint, Fluent $column): ?string
    {
        if (! is_null($virtualAs = $column->virtualAsJson)) {
            if ($this->isJsonSelector($virtualAs)) {
                $virtualAs = $this->wrapJsonSelector($virtualAs);
            }

            return " as ({$virtualAs})";
        }

        if (! is_null($column->virtualAs)) {
            return " as ({$column->virtualAs})";
        }

        return null;
    }

    /**
     * Get the SQL for a generated stored column modifier.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function modifyStoredAs(Blueprint $blueprint, Fluent $column): ?string
    {
        if (! is_null($storedAs = $column->storedAsJson)) {
            if ($this->isJsonSelector($storedAs)) {
                $storedAs = $this->wrapJsonSelector($storedAs);
            }

            return " as ({$storedAs}) stored";
        }

        if (! is_null($column->storedAs)) {
            return " as ({$column->storedAs}) stored";
        }

        return null;
    }

    /**
     * Determine if the given string is a JSON selector.
     */
    protected function isJsonSelector(string $value): bool
    {
        return str_contains($value, '->');
    }

    /**
     * Wrap the given JSON selector.
     */
    protected function wrapJsonSelector(string $value): string
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($value);

        return 'json_extract(' . $field . $path . ')';
    }

    /**
     * Split the given JSON selector into the field and the optional path and wrap them separately.
     */
    protected function wrapJsonFieldAndPath(string $column): array
    {
        $parts = explode('->', $column, 2);

        $field = $this->wrap($parts[0]);

        $path = count($parts) > 1 ? ', ' . $this->wrapJsonPath($parts[1], '->') : '';

        return [$field, $path];
    }

    /**
     * Wrap the given JSON path.
     */
    protected function wrapJsonPath(string $value, string $delimiter = '->'): string
    {
        $value = preg_replace("/([\\\\]+)?\\'/", "''", $value);

        $jsonPath = Collection::make(explode($delimiter, $value))
            ->map(fn ($segment) => $this->wrapJsonPathSegment($segment))
            ->implode('.');

        return "'$" . (str_starts_with($jsonPath, '[') ? '' : '.') . $jsonPath . "'";
    }

    /**
     * Wrap the given JSON path segment.
     */
    protected function wrapJsonPathSegment(string $segment): string
    {
        if (preg_match('/(\[[^\]]+\])+$/', $segment, $parts)) {
            $key = Str::beforeLast($segment, $parts[0]);

            if (! empty($key)) {
                return '"' . $key . '"' . $parts[0];
            }

            return $parts[0];
        }

        return '"' . $segment . '"';
    }

    /**
     * Get the SQL for a nullable column modifier.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function modifyNullable(Blueprint $blueprint, Fluent $column): ?string
    {
        if (is_null($column->virtualAs) && is_null($column->storedAs)) {
            return $column->nullable ? '' : ' not null';
        }

        if ($column->nullable === false) {
            return ' not null';
        }

        return null;
    }

    /**
     * Get the SQL for a default column modifier.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function modifyDefault(Blueprint $blueprint, Fluent $column): ?string
    {
        if (! is_null($column->default) && is_null($column->virtualAs) && is_null($column->storedAs)) {
            return ' default ' . $this->getDefaultValue($column->default);
        }

        return null;
    }

    /**
     * Get the SQL for an auto-increment column modifier.
     *
     * @param \SwooleTW\Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $column
     */
    protected function modifyIncrement(Blueprint $blueprint, Fluent $column): ?string
    {
        if (in_array($column->type, $this->serials) && $column->autoIncrement) {
            return ' primary key autoincrement';
        }

        return null;
    }
}
