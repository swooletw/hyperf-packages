<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console\Contracts;

use SwooleTW\Hyperf\Foundation\Console\Scheduling\Schedule;

interface Kernel
{
    /**
     * Define the application's command schedule.
     */
    public function schedule(Schedule $schedule): void;

    /**
     * Register the commands for the application.
     */
    public function commands(): void;

    /**
     * Add loadPaths in the given directory.
     *
     * @param array|string $paths
     */
    public function load($paths): void;

    /**
     * Get loadPaths for the application.
     */
    public function getLoadPaths(): array;
}
