<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use SwooleTW\Hyperf\Cache\Contracts\Factory;
use SwooleTW\Hyperf\Cache\Contracts\Store;
use SwooleTW\Hyperf\Cache\Listeners\CreateSwooleTable;
use SwooleTW\Hyperf\Cache\Listeners\CreateTimer;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Factory::class => CacheManager::class,
                Store::class => fn ($container) => $container->get(CacheManager::class)->driver(),
            ],
            'listeners' => [
                CreateSwooleTable::class,
                CreateTimer::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for cache.',
                    'source' => __DIR__ . '/../publish/laravel_cache.php',
                    'destination' => BASE_PATH . '/config/autoload/laravel_cache.php',
                ],
            ],
        ];
    }
}
