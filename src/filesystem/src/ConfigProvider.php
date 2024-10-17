<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Filesystem;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for filesystem.',
                    'source' => __DIR__ . '/../publish/filesystems.php',
                    'destination' => BASE_PATH . '/config/autoload/filesystems.php',
                ],
            ],
        ];
    }
}
