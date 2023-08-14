<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Database\Connectors;

use Hyperf\Database\Connection;
use Hyperf\Database\Connectors\ConnectionFactory as HyperfConnectionFactory;
use Hyperf\Database\Connectors\MySqlConnector;
use Hyperf\Database\MySqlConnection;
use InvalidArgumentException;
use SwooleTW\Hyperf\Database\SQLiteConnection;

class ConnectionFactory extends HyperfConnectionFactory
{
    /**
     * Create a connector instance based on the configuration.
     *
     * @return ConnectorInterface
     * @throws InvalidArgumentException
     */
    public function createConnector(array $config)
    {
        if (! isset($config['driver'])) {
            throw new InvalidArgumentException('A driver must be specified.');
        }

        if ($this->container->has($key = "db.connector.{$config['driver']}")) {
            return $this->container->get($key);
        }

        return match ($config['driver']) {
            'mysql' => new MySqlConnector(),
            'sqlite' => new SQLiteConnector(),
            default => throw new InvalidArgumentException("Unsupported driver [{$config['driver']}]"),
        };
    }

    /**
     * Create a new connection instance.
     *
     * @param string $driver
     * @param Closure|PDO $connection
     * @param string $database
     * @param string $prefix
     * @return \Hyperf\Database\Connection
     * @throws InvalidArgumentException
     */
    protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
    {
        if ($resolver = Connection::getResolver($driver)) {
            return $resolver($connection, $database, $prefix, $config);
        }

        switch ($driver) {
            case 'mysql':
                return new MySqlConnection($connection, $database, $prefix, $config);
            case 'sqlite':
                return new SQLiteConnection($connection, $database, $prefix, $config);
        }

        throw new InvalidArgumentException("Unsupported driver [{$driver}]");
    }
}
