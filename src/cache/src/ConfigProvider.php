<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use SwooleTW\Hyperf\Cache\CacheManager;
use SwooleTW\Hyperf\Cache\Contracts\Repository;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Repository::class => CacheManager::class,
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
