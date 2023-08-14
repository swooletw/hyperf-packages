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
            return new SQLiteConnection($connection, $database, $prefix, $config);
        });
    }
}
