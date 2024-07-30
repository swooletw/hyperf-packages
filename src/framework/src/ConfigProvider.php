<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf;

use Hyperf\Database\Commands\Migrations\BaseCommand as MigrationBaseCommand;
use Hyperf\Database\Commands\ModelCommand as HyperfModelCommand;
use Hyperf\Database\Commands\Seeders\BaseCommand as SeederBaseCommand;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Model\Factory as HyperfDatabaseFactory;
use Hyperf\Database\Schema\Schema;
use Hyperf\Dispatcher\HttpRequestHandler;
use SwooleTW\Hyperf\Database\Commands\ModelCommand;
use SwooleTW\Hyperf\Database\Eloquent\Factories\FactoryInvoker as DatabaseFactoryInvoker;

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
                HyperfDatabaseFactory::class => DatabaseFactoryInvoker::class,
            ],
            'commands' => $commands,
            'annotations' => [
                'scan' => [
                    'class_map' => [
                        Migration::class => __DIR__ . '/../class_map/Database/Migrations/Migration.php',
                        Schema::class => __DIR__ . '/../class_map/Database/Schema/Schema.php',
                        MigrationBaseCommand::class => __DIR__ . '/../class_map/Database/Commands/Migrations/BaseCommand.php',
                        SeederBaseCommand::class => __DIR__ . '/../class_map/Database/Commands/Seeders/BaseCommand.php',
                        HttpRequestHandler::class => __DIR__ . '/../class_map/Dispatcher/HttpRequestHandler.php',
                    ],
                ],
            ],
        ];
    }
}
