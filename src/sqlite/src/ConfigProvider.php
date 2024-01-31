<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Database;

use Hyperf\Database\Connection;
use SwooleTW\Hyperf\Database\Connectors\SQLiteConnector;

class ConfigProvider
{
    public function __invoke(): array
    {
        $this->resolveSqliteConnection();

        return [
            'dependencies' => [
                'db.connector.sqlite' => SQLiteConnector::class,
            ],
        ];
    }

    protected function resolveSqliteConnection()
    {
        Connection::resolverFor('sqlite', function ($connection, $database, $prefix, $config) {
            if ($config['database'] === ':memory:') {
                $connection = $this->createPersistentPdoResolver($connection, $config);
            }

            return new SQLiteConnection($connection, $database, $prefix, $config);
        });
    }

    private function createPersistentPdoResolver($connection, $config)
    {
        return function () use ($config, $connection) {
            $key = "sqlite.presistent.pdo.{$config['name']}";

            if (! app()->has($key)) {
                app()->instance($key, call_user_func($connection));
            }

            return app($key);
        };
    }
}
