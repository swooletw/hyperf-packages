<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf;

use Hyperf\Database\Commands\Migrations\BaseCommand as MigrationBaseCommand;
use Hyperf\Database\Commands\Seeders\BaseCommand as SeederBaseCommand;
use Hyperf\Database\Commands\Migrations\FreshCommand;
use Hyperf\Database\Commands\Migrations\InstallCommand;
use Hyperf\Database\Commands\Migrations\MigrateCommand;
use Hyperf\Database\Commands\Migrations\RefreshCommand;
use Hyperf\Database\Commands\Migrations\ResetCommand;
use Hyperf\Database\Commands\Migrations\RollbackCommand;
use Hyperf\Database\Commands\Migrations\StatusCommand;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Migrations\MigrationCreator as HyperfMigrationCreator;
use Hyperf\Database\Model\Factory as HyperfDatabaseFactory;
use SwooleTW\Hyperf\Database\Eloquent\Factories\FactoryInvoker as DatabaseFactoryInvoker;
use SwooleTW\Hyperf\Database\Migrations\MigrationCreator;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                HyperfDatabaseFactory::class => DatabaseFactoryInvoker::class,
                HyperfMigrationCreator::class => MigrationCreator::class,
            ],
            'commands' => [
                InstallCommand::class,
                MigrateCommand::class,
                FreshCommand::class,
                RefreshCommand::class,
                ResetCommand::class,
                RollbackCommand::class,
                StatusCommand::class,
            ];
            'annotations' => [
                'scan' => [
                    'class_map' => [
                        Migration::class => __DIR__ . '/../class_map/Database/Migrations/Migration.php',
                        MigrationBaseCommand::class => __DIR__ . '/../class_map/Database/Commands/Migrations/BaseCommand.php',
                        SeederBaseCommand::class => __DIR__ . '/../class_map/Database/Commands/Seeders/BaseCommand.php',
                    ],
                ],
            ],
        ];
    }
}
