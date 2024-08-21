<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth;

use SwooleTW\Hyperf\Auth\Access\GateFactory;
use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;
use SwooleTW\Hyperf\Auth\Contracts\FactoryContract;
use SwooleTW\Hyperf\Auth\Contracts\Gate as GateContract;
use SwooleTW\Hyperf\Auth\Contracts\Guard;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                FactoryContract::class => AuthManager::class,
                Authenticatable::class => UserResolver::class,
                Guard::class => fn ($container) => $container->get(FactoryContract::class)->guard(),
                GateContract::class => GateFactory::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for auth.',
                    'source' => __DIR__ . '/../publish/auth.php',
                    'destination' => BASE_PATH . '/config/autoload/auth.php',
                ],
            ],
        ];
    }
}
