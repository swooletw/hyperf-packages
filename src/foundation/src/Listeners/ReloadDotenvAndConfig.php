<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Listeners;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Support\DotenvManager;
use Psr\Container\ContainerInterface;

class ReloadDotenvAndConfig implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
        ];
    }

    public function process(object $event): void
    {
        $this->reloadDotenv();
        $this->reloadConfig();
    }

    protected function reloadConfig(): void
    {
        $this->container->unbind(ConfigInterface::class);
    }

    protected function reloadDotenv(): void
    {
        if (file_exists(BASE_PATH . '/.env')) {
            DotenvManager::reload([BASE_PATH]);
        }
    }
}
