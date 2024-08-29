<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Closure;
use Hyperf\Command\ClosureCommand;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Application as ApplicationContract;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel as KernelContract;
use SwooleTW\Hyperf\Foundation\Console\Scheduling\Schedule;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method static void bootstrap()
 * @method static void schedule(Schedule $schedule)
 * @method static void commands()
 * @method static ClosureCommand command(string $signature, Closure $callback)
 * @method static void load(array|string $paths)
 * @method static static addCommands(array $commands)
 * @method static static addCommandPaths(array $paths)
 * @method static static addCommandRoutePaths(array $paths)
 * @method static array getLoadedPaths()
 * @method static void registerCommand(string $command)
 * @method static int call(string $command, array $parameters = [], ?OutputInterface $outputBuffer = null)
 * @method static array all()
 * @method static string output()
 * @method static void setArtisan(ApplicationContract $artisan)
 * @method static ApplicationContract getArtisan()
 *
 * @see Kernel
 */
class Artisan extends Facade
{
    protected static function getFacadeAccessor()
    {
        return KernelContract::class;
    }
}
