<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console;

use Symfony\Component\Console\Command\Command;

class CommandReplacer
{
    protected static array $commands = [
        'start' => 'serve',
        'gen:amqp-consumer' => 'make:amqp-consumer',
        'gen:amqp-producer' => 'make:amqp-producer',
        'gen:aspect' => 'make:aspect',
        'gen:class' => 'make:class',
        'gen:command' => 'make:command',
        'gen:constant' => 'make:constant',
        'gen:controller' => 'make:controller',
        'gen:job' => 'make:job',
        'gen:kafka-consumer' => 'make:kafka-consumer',
        'gen:listener' => 'make:listener',
        'gen:middleware' => 'make:middleware',
        'gen:migration' => 'make:migration',
        'gen:model' => 'make:model',
        'gen:nats-consumer' => 'make:nats-consumer',
        'gen:nsq-consumer' => 'make:nsq-consumer',
        'gen:observer' => 'make:observer',
        'gen:process' => 'make:process',
        'gen:request' => 'make:request',
        'gen:resource' => 'make:resource',
        'gen:seeder' => 'make:seeder',
        'gen:swagger' => 'make:swagger',
        'gen:migration-from-database' => 'make:migration-from-database',
        'gen:view-engine-cache' => 'make:view-engine-cache',
        'gen:swagger-schema' => 'make:swagger-schema',
        'crontab:run' => [
            'name' => 'schedule:run',
            'description' => 'Run the scheduled commands',
        ],
    ];

    public static function replace(Command $command, bool $remainAlias = true): Command
    {
        $commandName = $command->getName();
        if (! $replace = static::$commands[$commandName] ?? null) {
            return $command;
        }

        $command->setName($replace['name'] ?? $replace);
        if ($remainAlias) {
            $command->setAliases([$commandName]);
        }

        if ($description = $replace['description'] ?? null) {
            $command->setDescription($description);
        }

        return $command;
    }
}
