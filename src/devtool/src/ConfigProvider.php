<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Devtool;

use Hyperf\Devtool\Generator\GeneratorCommand;
use SwooleTW\Hyperf\Devtool\Commands\EventListCommand;
use SwooleTW\Hyperf\Devtool\Generator\ComponentCommand;
use SwooleTW\Hyperf\Devtool\Generator\ConsoleCommand;
use SwooleTW\Hyperf\Devtool\Generator\EventCommand;
use SwooleTW\Hyperf\Devtool\Generator\FactoryCommand;
use SwooleTW\Hyperf\Devtool\Generator\ListenerCommand;
use SwooleTW\Hyperf\Devtool\Generator\ModelCommand;
use SwooleTW\Hyperf\Devtool\Generator\ProviderCommand;
use SwooleTW\Hyperf\Devtool\Generator\RequestCommand;
use SwooleTW\Hyperf\Devtool\Generator\RuleCommand;
use SwooleTW\Hyperf\Devtool\Generator\SeederCommand;
use SwooleTW\Hyperf\Devtool\Generator\SessionTableCommand;
use SwooleTW\Hyperf\Devtool\Generator\TestCommand;

class ConfigProvider
{
    public function __invoke(): array
    {
        if (! class_exists(GeneratorCommand::class)) {
            return [];
        }

        return [
            'commands' => [
                ProviderCommand::class,
                EventCommand::class,
                ListenerCommand::class,
                ComponentCommand::class,
                TestCommand::class,
                SessionTableCommand::class,
                RuleCommand::class,
                ConsoleCommand::class,
                ModelCommand::class,
                FactoryCommand::class,
                SeederCommand::class,
                EventListCommand::class,
                RequestCommand::class,
            ],
        ];
    }
}
