<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache\Processes;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
use Swoole\Timer;
use SwooleTW\Hyperf\Cache\SwooleStore;
use SwooleTW\Hyperf\Support\Facades\Cache;

class EvictRecords extends AbstractProcess
{
    public function handle(): void
    {
        $configs = $this->container->get(ConfigInterface::class)->get('laravel_cache.stores');

        collect($configs)->filter(function (array $config) {
            return $config['driver'] === 'swoole';
        })->each(function (array $config, string $name) {
            Timer::tick($config['eviction_interval'] ?? 10000, function () use ($config, $name) {
                /** @var SwooleStore */
                $store = Cache::store($name)->getStore();

                $store->evictRecordsWhenMemoryLimitIsReached(
                    $config['memory_limit_buffer'] ?? 0.05,
                    $config['eviction_policy'] ?? SwooleStore::EVICTION_POLICY_LRU,
                    $config['eviction_quantity'] ?? 10
                );
            });
        });

        while (ProcessManager::isRunning()) {
            sleep(1);
        }
    }
}
