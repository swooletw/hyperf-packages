<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Foundation\Testing;

class BootstrapConfigProvider
{
    protected static $configProviders = [
        \Hyperf\Command\ConfigProvider::class,
        \Hyperf\Crontab\ConfigProvider::class,
        \Hyperf\Database\SQLite\ConfigProvider::class,
        \Hyperf\DbConnection\ConfigProvider::class,
        \Hyperf\Di\ConfigProvider::class,
        \Hyperf\Dispatcher\ConfigProvider::class,
        \Hyperf\Engine\ConfigProvider::class,
        \Hyperf\Event\ConfigProvider::class,
        \Hyperf\ExceptionHandler\ConfigProvider::class,
        \Hyperf\Framework\ConfigProvider::class,
        \Hyperf\Guzzle\ConfigProvider::class,
        \Hyperf\HttpMessage\ConfigProvider::class,
        \Hyperf\HttpServer\ConfigProvider::class,
        \Hyperf\Memory\ConfigProvider::class,
        \Hyperf\ModelListener\ConfigProvider::class,
        \Hyperf\Paginator\ConfigProvider::class,
        \Hyperf\Pool\ConfigProvider::class,
        \Hyperf\Process\ConfigProvider::class,
        \Hyperf\Redis\ConfigProvider::class,
        \Hyperf\Serializer\ConfigProvider::class,
        \Hyperf\Server\ConfigProvider::class,
        \Hyperf\Signal\ConfigProvider::class,
        \Hyperf\Translation\ConfigProvider::class,
        \Hyperf\Validation\ConfigProvider::class,
        \SwooleTW\Hyperf\ConfigProvider::class,
        \SwooleTW\Hyperf\Auth\ConfigProvider::class,
        \SwooleTW\Hyperf\Bus\ConfigProvider::class,
        \SwooleTW\Hyperf\Cache\ConfigProvider::class,
        \SwooleTW\Hyperf\Cookie\ConfigProvider::class,
        \SwooleTW\Hyperf\Config\ConfigProvider::class,
        \SwooleTW\Hyperf\Dispatcher\ConfigProvider::class,
        \SwooleTW\Hyperf\Encryption\ConfigProvider::class,
        \SwooleTW\Hyperf\Event\ConfigProvider::class,
        \SwooleTW\Hyperf\Foundation\ConfigProvider::class,
        \SwooleTW\Hyperf\Hashing\ConfigProvider::class,
        \SwooleTW\Hyperf\Http\ConfigProvider::class,
        \SwooleTW\Hyperf\JWT\ConfigProvider::class,
        \SwooleTW\Hyperf\Log\ConfigProvider::class,
        \SwooleTW\Hyperf\Mail\ConfigProvider::class,
        \SwooleTW\Hyperf\Notifications\ConfigProvider::class,
        \SwooleTW\Hyperf\Queue\ConfigProvider::class,
        \SwooleTW\Hyperf\Router\ConfigProvider::class,
        \SwooleTW\Hyperf\Session\ConfigProvider::class,
    ];

    public static function get(): array
    {
        if (class_exists($devtoolClass = \Hyperf\Devtool\ConfigProvider::class)) {
            return array_merge(self::$configProviders, [$devtoolClass]);
        }

        return self::$configProviders;
    }
}
