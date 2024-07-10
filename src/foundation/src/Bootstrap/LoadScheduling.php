<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Bootstrap;

use Hyperf\Contract\ConfigInterface;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel as KernelContract;
use SwooleTW\Hyperf\Foundation\Console\Scheduling\Schedule;
use SwooleTW\Hyperf\Foundation\Contracts\Application as ApplicationContract;

class LoadScheduling
{
    /**
     * Load Scheduling.
     */
    public function bootstrap(ApplicationContract $app): void
    {
        if (! $app->has(KernelContract::class)) {
            return;
        }

        $schedule = new Schedule($app);
        $app->get(KernelContract::class)
            ->schedule($schedule);
        $commands = $schedule->getCommands();
        $crontabs = $app->get(ConfigInterface::class)
            ->get('crontab.crontab', []);
        $app->get(ConfigInterface::class)
            ->set('crontab.crontab', array_merge($crontabs, $commands));
    }
}
