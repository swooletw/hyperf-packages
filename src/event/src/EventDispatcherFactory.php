<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Event;

use Hyperf\AsyncQueue\Driver\DriverFactory as QueueFactory;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class EventDispatcherFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $listeners = $container->get(ListenerProviderInterface::class);
        $stdoutLogger = $container->get(StdoutLoggerInterface::class);
        $dispatcher = new EventDispatcher($listeners, $stdoutLogger, $container);

        $dispatcher->setQueueResolver(fn () => $container->get(QueueFactory::class));

        return $dispatcher;
    }
}
