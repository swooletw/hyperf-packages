<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Broadcasting;

use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Broadcasting\Contracts\Factory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Factory::class => fn (ContainerInterface $container) => new BroadcastManager($container),
            ],
        ];
    }
}
