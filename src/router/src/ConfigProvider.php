<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use Hyperf\HttpServer\Router\DispatcherFactory as HyperfDispatcherFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                HyperfDispatcherFactory::class => DispatcherFactory::class,
            ],
        ];
    }
}
