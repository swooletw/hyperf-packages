<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache\Listeners;

use Hyperf\Framework\Event\BeforeServerStart;
use SwooleTW\Hyperf\Cache\SwooleTableManager;

class CreateSwooleTable extends BaseListener
{
    public function listen(): array
    {
        return [
            BeforeServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        $this->swooleStores()->each(function (array $config) {
            $this->container->get(SwooleTableManager::class)->get($config['table']);
        });
    }
}
