<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Bootstrap;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel as KernelContract;
use SwooleTW\Hyperf\Foundation\Console\Scheduling\Schedule;

class LoadCrontabs
{
    /**
     * Load Crontabs
     *
     * @param  \Psr\Container\ContainerInterface  $app
     * @return void
     */
    public function bootstrap(ContainerInterface $app): void
    {
        if (! $app->has(KernelContract::class)) {
            return;
        }

        $schedule = new Schedule($app);
        $kernel = $app->get(KernelContract::class)
            ->schedule($schedule);
        $commands = $schedule->getCommands();
        $crontabs = $app->get(ConfigInterface::class)
            ->get('crontab.crontab', []);
        $app->get(ConfigInterface::class)
            ->set('crontab.crontab', array_merge($crontabs, $commands));
    }
}
