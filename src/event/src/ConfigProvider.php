<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                EventDispatcherInterface::class => EventDispatcherFactory::class,
                ListenerProviderInterface::class => ListenerProviderFactory::class,
            ],
        ];
    }
}
