<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Queue;

use Laravel\SerializableClosure\SerializableClosure;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Queue\Console\ClearCommand;
use SwooleTW\Hyperf\Queue\Console\FlushFailedCommand;
use SwooleTW\Hyperf\Queue\Console\ForgetFailedCommand;
use SwooleTW\Hyperf\Queue\Console\ListenCommand;
use SwooleTW\Hyperf\Queue\Console\ListFailedCommand;
use SwooleTW\Hyperf\Queue\Console\MonitorCommand;
use SwooleTW\Hyperf\Queue\Console\PruneBatchesCommand;
use SwooleTW\Hyperf\Queue\Console\PruneFailedJobsCommand;
use SwooleTW\Hyperf\Queue\Console\RestartCommand;
use SwooleTW\Hyperf\Queue\Console\RetryBatchCommand;
use SwooleTW\Hyperf\Queue\Console\RetryCommand;
use SwooleTW\Hyperf\Queue\Console\WorkCommand;
use SwooleTW\Hyperf\Queue\Contracts\Factory as FactoryContract;
use SwooleTW\Hyperf\Queue\Contracts\Queue;
use SwooleTW\Hyperf\Queue\Failed\FailedJobProviderFactory;
use SwooleTW\Hyperf\Queue\Failed\FailedJobProviderInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        $this->configureSerializableClosureUses();

        return [
            'dependencies' => [
                FactoryContract::class => QueueManager::class,
                Queue::class => fn (ContainerInterface $container) => $container->get(FactoryContract::class)->connection(),
                FailedJobProviderInterface::class => FailedJobProviderFactory::class,
                Listener::class => fn (ContainerInterface $container) => new Listener($this->getBasePath($container)),
                Worker::class => WorkerFactory::class,
            ],
            'commands' => [
                WorkCommand::class,
                ClearCommand::class,
                FlushFailedCommand::class,
                ForgetFailedCommand::class,
                ListFailedCommand::class,
                ListenCommand::class,
                MonitorCommand::class,
                PruneBatchesCommand::class,
                PruneFailedJobsCommand::class,
                RestartCommand::class,
                RetryBatchCommand::class,
                RetryCommand::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file of queue.',
                    'source' => __DIR__ . '/../publish/queue.php',
                    'destination' => BASE_PATH . '/config/autoload/queue.php',
                ],
            ],
        ];
    }

    /**
     * Configure serializable closures uses.
     */
    protected function configureSerializableClosureUses(): void
    {
        SerializableClosure::transformUseVariablesUsing(function ($data) {
            foreach ($data as $key => $value) {
                /* @phpstan-ignore-next-line */
                $data[$key] = $this->getSerializedPropertyValue($value);
            }

            return $data;
        });

        SerializableClosure::resolveUseVariablesUsing(function ($data) {
            foreach ($data as $key => $value) {
                /* @phpstan-ignore-next-line */
                $data[$key] = $this->getRestoredPropertyValue($value);
            }

            return $data;
        });
    }

    protected function getBasePath(ContainerInterface $container): string
    {
        return method_exists($container, 'basePath')
            ? $container->basePath()
            : BASE_PATH;
    }
}
