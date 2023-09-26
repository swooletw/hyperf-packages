<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Dispatcher;

use Hyperf\Dispatcher\HttpDispatcher as HyperfHttpDispatcher;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                HyperfHttpDispatcher::class => HttpDispatcher::class,
            ],
        ];
    }
}
