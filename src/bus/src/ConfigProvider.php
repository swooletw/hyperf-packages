<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Bus;

use SwooleTW\Hyperf\Bus\Contracts\BatchRepository;
use SwooleTW\Hyperf\Bus\Contracts\Dispatcher as DispatcherContract;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                DispatcherContract::class => DispatcherFactory::class,
                BatchRepository::class => DatabaseBatchRepository::class,
                DatabaseBatchRepository::class => DatabaseBatchRepositoryFactory::class,
            ],
        ];
    }
}
