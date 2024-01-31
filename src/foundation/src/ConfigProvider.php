<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation;

use Hyperf\Database\Commands\Migrations\BaseCommand as MigrationBaseCommand;
use Hyperf\Database\Commands\Seeders\BaseCommand as SeederBaseCommand;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Model\Factory as DatabaseFactory;
use SwooleTW\Hyperf\Foundation\Console\Commands\ServerReloadCommand;
use SwooleTW\Hyperf\Foundation\Listeners\CreateWorkerRestartTimesCounter;
use SwooleTW\Hyperf\Foundation\Listeners\ReloadDotenvAndConfig;
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
            'listeners' => [
                CreateWorkerRestartTimesCounter::class,
                ReloadDotenvAndConfig::class,
            ],
            'commands' => [
                QueueWorkCommand::class,
                ServerReloadCommand::class,
            ],
            'annotations' => [
                'scan' => [
                    'class_map' => [
                        Migration::class => __DIR__ . '/../class_map/Database/Migrations/Migration.php',
                        MigrationBaseCommand::class => __DIR__ . '/../class_map/Database/Commands/Migrations/BaseCommand.php',
                        SeederBaseCommand::class => __DIR__ . '/../class_map/Database/Commands/Seeders/BaseCommand.php',
                    ],
                ],
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
