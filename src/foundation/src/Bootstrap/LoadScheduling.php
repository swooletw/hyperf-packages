<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Bootstrap;

use Hyperf\Contract\ConfigInterface;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Application as ApplicationContract;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel as KernelContract;
use SwooleTW\Hyperf\Foundation\Console\Scheduling\Schedule;

class LoadScheduling
{
    /**
     * Load Scheduling.
     */
    public function bootstrap(ApplicationContract $app): void
    {
        $container = $app->getContainer();
        if (! $container->has(KernelContract::class)) {
            return;
        }

        $schedule = new Schedule($container);
        $container->get(KernelContract::class)
            ->schedule($schedule);
        $commands = $schedule->getCommands();
        $crontabs = $container->get(ConfigInterface::class)
            ->get('crontab.crontab', []);
        $container->get(ConfigInterface::class)
            ->set('crontab.crontab', array_merge($crontabs, $commands));
    }
}
