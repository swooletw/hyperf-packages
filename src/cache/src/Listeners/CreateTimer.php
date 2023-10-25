<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache\Listeners;

use Hyperf\Framework\Event\OnManagerStart;
use Swoole\Timer;
use SwooleTW\Hyperf\Support\Facades\Cache;

class CreateTimer extends BaseListener
{
    public function listen(): array
    {
        return [
            OnManagerStart::class,
        ];
    }

    public function process(object $event): void
    {
        $this->swooleStores()->each(function (array $config, string $name) {
            Timer::tick($config['eviction_interval'] ?? 10000, function () use ($name) {
                /** @var SwooleStore */
                $store = Cache::store($name)->getStore();

                $store->evictRecords();
            });
        });
    }
}
