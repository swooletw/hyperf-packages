<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console;

use Hyperf\Collection\Arr;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel as KernelContract;
use SwooleTW\Hyperf\Foundation\Console\Scheduling\Schedule;

class Kernel implements KernelContract
{
    public function __construct(
        protected ContainerInterface $app,
        protected array $loadPaths = []
    ) {}

    /**
     * Define the application's command schedule.
     */
    public function schedule(Schedule $schedule): void {}

    /**
     * Register the commands for the application.
     */
    public function commands(): void
    {
        $this->load(app_path('Console/Commands'));
    }

    /**
     * Add loadPaths in the given directory.
     *
     * @param array|string $paths
     */
    public function load($paths): void
    {
        $paths = array_unique(Arr::wrap($paths));

        $paths = array_filter($paths, function ($path) {
            return is_dir($path);
        });

        if (empty($paths)) {
            return;
        }

        $this->loadPaths = array_values(
            array_unique(array_merge($this->loadPaths, $paths))
        );
    }

    /**
     * Get loadPaths for the application.
     */
    public function getLoadPaths(): array
    {
        return $this->loadPaths;
    }
}
