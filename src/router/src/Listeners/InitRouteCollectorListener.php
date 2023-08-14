<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router\Listeners;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Router\RouteCollector;

class InitRouteCollectorListener implements ListenerInterface
{
    public function __construct(
        protected ContainerInterface $container
    ) {
    }

    public function listen(): array
    {
        return [
            MainCoroutineServerStart::class,
            BeforeWorkerStart::class,
        ];
    }

    public function process(object $event): void
    {
        $this->container->get(RouteCollector::class);
    }
}
