<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console\Contracts;

use Exception;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Application as ApplicationContract;
use SwooleTW\Hyperf\Foundation\Console\Scheduling\Schedule;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

    /**
     * Bootstrap the application for artisan commands.
     */
    public function bootstrap(): void;

    /**
     * Register the given command with the console application.
     */
    public function registerCommand(string $command);

    /**
     * Run an Artisan console command by name.
     *
     * @throws \Symfony\Component\Console\Exception\CommandNotFoundException
     */
    public function call(string $command, array $parameters = [], ?OutputInterface $outputBuffer = null): int;

    /**
     * Get all of the commands registered with the console.
     */
    public function all(): array;

    /**
     * Get the output for the last run command.
     */
    public function output(): string;

    /**
     * Set the Artisan application instance.
     */
    public function setArtisan(ApplicationContract $artisan): void;

    /**
     * Runs the current application.
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @throws Exception When running fails. Bypass this when {@link setCatchExceptions()}.
     */
    public function run(?InputInterface $input = null, ?OutputInterface $output = null): int;
}
