<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use Hyperf\Dispatcher\HttpDispatcher as HyperfHttpDispatcher;
use SwooleTW\Hyperf\Foundation\Testing\Dispatcher\HttpDispatcher;
use SwooleTW\Hyperf\Router\Listeners\InitRouteCollectorListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                InitRouteCollectorListener::class,
            ],
            'dependencies' => [
                HyperfHttpDispatcher::class => HttpDispatcher::class,
            ],
        ];
    }
}
