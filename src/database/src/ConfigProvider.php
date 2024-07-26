<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Database;

use Hyperf\Database\Commands\Migrations\BaseCommand as MigrationBaseCommand;
use Hyperf\Database\Commands\ModelCommand as HyperfModelCommand;
use Hyperf\Database\Commands\Seeders\BaseCommand as SeederBaseCommand;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Model\Factory as DatabaseFactory;
use SwooleTW\Hyperf\Database\Commands\ModelCommand;
use SwooleTW\Hyperf\Database\Eloquent\Factories\FactoryInvoker;

class ConfigProvider
{
    public function __invoke(): array
    {
        $commands = [];
        if (class_exists(HyperfModelCommand::class)) {
            $commands[] = ModelCommand::class;
        }

        return [
            'dependencies' => [
                DatabaseFactory::class => FactoryInvoker::class,
            ],
            'commands' => $commands,
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
