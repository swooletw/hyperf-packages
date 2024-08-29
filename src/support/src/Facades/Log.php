<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Psr\Log\LoggerInterface;
use SwooleTW\Hyperf\Log\LogManager;

/**
 * @method static LoggerInterface build(array $config)
 * @method static LoggerInterface stack(array $channels, ?string $channel = null)
 * @method static LoggerInterface channel(?string $channel = null)
 * @method static LoggerInterface driver(?string $driver = null)
 * @method static ?string getDefaultDriver()
 * @method static void setDefaultDriver(string $name)
 * @method static LogManager extend(string $driver, \Closure $callback)
 * @method static void forgetChannel(?string $driver = null)
 * @method static array getChannels()
 * @method static void emergency(string|\Stringable $message, array $context = [])
 * @method static void alert(string|\Stringable $message, array $context = [])
 * @method static void critical(string|\Stringable $message, array $context = [])
 * @method static void error(string|\Stringable $message, array $context = [])
 * @method static void warning(string|\Stringable $message, array $context = [])
 * @method static void notice(string|\Stringable $message, array $context = [])
 * @method static void info(string|\Stringable $message, array $context = [])
 * @method static void debug(string|\Stringable $message, array $context = [])
 * @method static void log($level, string|\Stringable $message, array $context = [])
 *
 * @see LogManager
 */
class Log extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LogManager::class;
    }
}
