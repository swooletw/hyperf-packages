<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Event;

use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use SwooleTW\Hyperf\Queue\Contracts\Factory as QueueFactoryContract;

class EventDispatcherFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $listeners = $container->get(ListenerProviderInterface::class);
        $stdoutLogger = $container->get(StdoutLoggerInterface::class);
        $dispatcher = new EventDispatcher($listeners, $stdoutLogger, $container);

        $dispatcher->setQueueResolver(fn () => $container->get(QueueFactoryContract::class));

        return $dispatcher;
    }
}
