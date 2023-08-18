<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use SwooleTW\Hyperf\Router\Listeners\InitRouteCollectorListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                InitRouteCollectorListener::class,
            ],
        ];
    }
}
