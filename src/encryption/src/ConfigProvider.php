<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Encryption;

use SwooleTW\Hyperf\Encryption\Contracts\Encrypter;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Encrypter::class => EncryptionFactory::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file of encryption.',
                    'source' => __DIR__ . '/../publish/encryption.php',
                    'destination' => BASE_PATH . '/config/autoload/encryption.php',
                ],
            ],
        ];
    }
}
