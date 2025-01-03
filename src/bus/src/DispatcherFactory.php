<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Bus;

use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Queue\Contracts\Factory as QueueFactoryContract;

class DispatcherFactory
{
    public function __invoke(ContainerInterface $container): Dispatcher
    {
        return new Dispatcher(
            $container,
            fn (?string $connection = null) => $container->get(QueueFactoryContract::class)->connection($connection)
        );
    }
}
