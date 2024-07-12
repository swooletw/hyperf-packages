<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console\Scheduling;

use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\Schedule as HyperfSchedule;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Schedule as ScheduleContract;

class Schedule implements ScheduleContract
{
    protected array $commands = [];

    public function __construct(
        protected ContainerInterface $app
    ) {}

    public function command(string $command, array $arguments = []): Crontab
    {
        $crontab = HyperfSchedule::command($command, $arguments);
        $this->commands[] = $crontab;

        return $crontab;
    }

    public function call(mixed $callable): Crontab
    {
        $crontab = HyperfSchedule::call($callable);
        $this->commands[] = $crontab;

        return $crontab;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }
}
