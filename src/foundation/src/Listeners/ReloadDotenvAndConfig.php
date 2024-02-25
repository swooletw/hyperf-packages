<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Listeners;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Foundation\Di\DotenvManager;
use SwooleTW\Hyperf\Watcher\Events\BeforeServerRestart;

class ReloadDotenvAndConfig implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container) {}

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
            BeforeServerRestart::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof BeforeWorkerStart
            && $event->workerId === 0
            && $this->container->get('server.stats.worker_restart_times')->add() === 0
        ) {
            return;
        }

        $this->reloadDotenv();
        $this->reloadConfig();
    }

    private function reloadDotenv(): void
    {
        DotenvManager::reload();
    }

    protected function reloadConfig(): void
    {
        $this->container->unbind(ConfigInterface::class);
    }
}
