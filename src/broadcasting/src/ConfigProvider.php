<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Broadcasting;

use SwooleTW\Hyperf\Broadcasting\Contracts\Factory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Factory::class => BroadcastManager::class,
            ],
        ];
    }
}
