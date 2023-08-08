<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Support\Concerns;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Coroutine;
use Hyperf\Server\ServerFactory;
use function Hyperf\Support\swoole_hook_flags;
use Psr\EventDispatcher\EventDispatcherInterface;

trait CanStartServer
{
    protected string $serverHost = '127.0.0.1';
    protected int $serverPort = 9601;
    protected ?array $processes = [];

    protected function setProcesses(array $processes = []): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->set('processes', $processes);
    }

    protected function runServer(?int $port = null): void
    {
        $serverFactory = $this->container->get(ServerFactory::class)
            ->setEventDispatcher($this->container->get(EventDispatcherInterface::class))
            ->setLogger($this->container->get(StdoutLoggerInterface::class));
        $serverFactory->configure(
            $this->getServerConfig($port ?: $this->serverPort)
        );

        Coroutine::set([
            'hook_flags' => swoole_hook_flags()
        ]);

        $serverFactory->start();
    }

    protected function getServerConfig(int $port): array
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->set('server.servers.0.host', $this->serverHost);
        $config->set('server.servers.0.port', $port);
        $config->set('server.settings.worker_num', 1);
        $config->set('server.settings.task_worker_num', 0);

        return $config->get('server');
    }
}
