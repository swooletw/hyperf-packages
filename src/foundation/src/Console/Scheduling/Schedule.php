<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console\Scheduling;

use Hyperf\Command\Command;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\Schedule as HyperfSchedule;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Schedule as ScheduleContract;
use SwooleTW\Hyperf\Foundation\Console\Parser;

class Schedule implements ScheduleContract
{
    protected array $commands = [];

    public function __construct(
        protected ContainerInterface $app
    ) {}

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

    public function call(mixed $callable): Crontab
    {
        $crontab = HyperfSchedule::call($callable);
        $this->commands[] = $crontab;

        return $crontab;
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
