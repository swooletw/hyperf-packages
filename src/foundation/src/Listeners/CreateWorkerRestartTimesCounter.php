<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Listeners;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeServerStart;
use Psr\Container\ContainerInterface;
use Swoole\Atomic;

class CreateWorkerRestartTimesCounter implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container) {}

    public function listen(): array
    {
        return [
            BeforeServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        $counter = new Atomic();

        $counter->set(-1);

        $this->container->set('server.stats.worker_restart_times', $counter);
    }
}
