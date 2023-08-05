<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Hashing;

use SwooleTW\Hyperf\Hashing\Contracts\Hasher;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Hasher::class => HashManager::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file of hashing.',
                    'source' => __DIR__ . '/../publish/hashing.php',
                    'destination' => BASE_PATH . '/config/autoload/hashing.php',
                ],
            ],
        ];
    }
}
