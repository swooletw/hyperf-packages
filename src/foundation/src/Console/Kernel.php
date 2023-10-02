<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console;

use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel as KernelContract;
use SwooleTW\Hyperf\Foundation\Console\Scheduling\Schedule;

class Kernel implements KernelContract
{
    public function __construct(
        protected ContainerInterface $app
    ) {}

    /**
     * Define the application's command schedule.
     */
    public function schedule(Schedule $schedule): void {}
}
