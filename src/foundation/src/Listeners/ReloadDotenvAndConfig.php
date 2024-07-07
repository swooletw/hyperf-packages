<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Listeners;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Psr\Container\ContainerInterface;
use Swoole\Atomic;
use SwooleTW\Hyperf\Foundation\Di\DotenvManager;

class ReloadDotenvAndConfig implements ListenerInterface
{
    protected static Atomic $restartCounter;

    public function __construct(protected ContainerInterface $container)
    {
        static::$restartCounter = new Atomic(0);
    }

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
        ];
    }

    public function process(object $event): void
    {
        echo static::$restartCounter->get() . PHP_EOL;
        if (
            $event instanceof BeforeWorkerStart
            && $event->workerId === 0
            && static::$restartCounter->get() === 0
        ) {
            static::$restartCounter->add();
            return;
        }

        static::$restartCounter->add();

        $this->reloadDotenv();
        $this->reloadConfig();
    }

    protected function reloadConfig(): void
    {
        $this->container->unbind(ConfigInterface::class);
    }

    protected function reloadDotenv(): void
    {
        DotenvManager::reload([BASE_PATH]);
    }
}
