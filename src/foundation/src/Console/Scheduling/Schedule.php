<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console\Scheduling;

use Hyperf\Command\Command;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\Schedule as HyperfSchedule;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Schedule as ScheduleContract;

class Schedule implements ScheduleContract
{
    protected array $crontabs = [];

    public function __construct(
        protected ContainerInterface $app
    ) {}

    public function command(string $command, array $parameters = []): Crontab
    {
        $crontab = $this->makeCrontab($command, $parameters);
        $this->crontabs[] = $crontab;

        return $crontab;
    }

    protected function makeCrontab(string $command, array $parameters = []): Crontab
    {
        $commandName = class_exists($command) && is_subclass_of($command, Command::class)
            ? $this->app->get($command)->getName()
            : $command;

        return (new Crontab())
            ->setName($commandName)
            ->setType('command')
            ->setCallback(array_merge(
                ['command' => $commandName],
                ['--disable-event-dispatcher' => true],
                $parameters
            ));
    }

    public function call(mixed $callable): Crontab
    {
        $crontab = HyperfSchedule::call($callable);
        $this->crontabs[] = $crontab;

        return $crontab;
    }

    public function getCrontabs(): array
    {
        return $this->crontabs;
    }
}
