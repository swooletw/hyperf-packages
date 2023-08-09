<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth;

use SwooleTW\Hyperf\Auth\AuthManager;
use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;
use SwooleTW\Hyperf\Auth\Contracts\FactoryContract;
use SwooleTW\Hyperf\Auth\UserResolver;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                FactoryContract::class => AuthManager::class,
                Authenticatable::class => UserResolver::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for cache.',
                    'source' => __DIR__ . '/../publish/auth.php',
                    'destination' => BASE_PATH . '/config/autoload/auth.php',
                ],
            ],
        ];
    }
}
