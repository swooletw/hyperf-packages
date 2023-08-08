<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation;

use SwooleTW\Hyperf\Foundation\Queue\Console\QueueWorkCommand;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'commands' => [
                QueueWorkCommand::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file of foundation.',
                    'source' => __DIR__ . '/../publish/app.php',
                    'destination' => BASE_PATH . '/config/autoload/app.php',
                ],
            ],
        ];
    }
}
