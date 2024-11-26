<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Queue;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Foundation\Exceptions\Contracts\ExceptionHandler as ExceptionHandlerContract;
use SwooleTW\Hyperf\Queue\Contracts\Factory as QueueManager;

class WorkerFactory
{
    public function __invoke(ContainerInterface $container): Worker
    {
        return new Worker(
            $container->get(QueueManager::class),
            $container->get(EventDispatcherInterface::class),
            $container->get(ExceptionHandlerContract::class),
            fn () => false,
        );
    }
}
