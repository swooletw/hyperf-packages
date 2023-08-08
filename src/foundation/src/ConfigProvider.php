<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation;

use Hyperf\Database\Model\Factory as DatabaseFactory;
use SwooleTW\Hyperf\Foundation\Console\Commands\ServerReloadCommand;
use SwooleTW\Hyperf\Foundation\Model\FactoryInvoker;
use SwooleTW\Hyperf\Foundation\Queue\Console\QueueWorkCommand;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                DatabaseFactory::class => FactoryInvoker::class,
            ],
            'commands' => [
                QueueWorkCommand::class,
                ServerReloadCommand::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file of foundation.',
                    'source' => __DIR__ . '/../publish/app.php',
                    'destination' => BASE_PATH . '/config/autoload/app.php',
                ],
            ],
        ];
    }
}
