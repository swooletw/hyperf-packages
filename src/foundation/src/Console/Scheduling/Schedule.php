<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console\Scheduling;

use Hyperf\Command\Command;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Foundation\Console\Parser;

class Schedule
{
    public const SUNDAY = 0;

    public const MONDAY = 1;

    public const TUESDAY = 2;

    public const WEDNESDAY = 3;

    public const THURSDAY = 4;

    public const FRIDAY = 5;

    public const SATURDAY = 6;

    protected array $commands = [];

    public function __construct(
        protected ContainerInterface $app
    ) {
    }

    /**
     * Add a new crontab.
     */
    public function command(string $command, array $parameters = []): Crontab
    {
        if (class_exists($command)
            && is_subclass_of($command, Command::class)
        ) {
            $command = $this->getClassCommand($command, $parameters);
            $this->commands[] = $command;

            return $command;
        }

        $command = $this->getStringCommand($command);
        $this->commands[] = $command;

        return $command;
    }

    public function callback(array $callback): Crontab
    {
        $command = (new Crontab())
            ->setName(uniqid())
            ->setType('callback')
            ->setCallback($callback);

        $this->commands[] = $command;

        return $command;
    }

    protected function getClassCommand(string $class, array $parameters = []): Crontab
    {
        $command = $this->app->get($class);
        return (new Crontab())
            ->setName($command = $command->getName())
            ->setType('command')
            ->setCallback(array_merge(
                ['--disable-event-dispatcher' => true],
                $parameters,
                ['command' => $command]
            ));
    }

    protected function getStringCommand(string $command): Crontab
    {
        $result = Parser::parse($command);
        return (new Crontab())
            ->setName($command)
            ->setType('command')
            ->setCallback(array_merge(
                ['--disable-event-dispatcher' => true],
                $result['arguments'] ?? [],
                $result['options'] ?? [],
                ['command' => $result['command'] ?? null]
            ));
    }

    public function getCommands(): array
    {
        return $this->commands;
    }
}
