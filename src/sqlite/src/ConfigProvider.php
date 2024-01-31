<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Database;

use Hyperf\Context\ApplicationContext;
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
            $container = ApplicationContext::getContainer();
            $key = "sqlite.presistent.pdo.{$config['name']}";

            if (! $container->has($key)) {
                $container->set($key, call_user_func($connection));
            }

            return $container->get($key);
        };
    }
}
