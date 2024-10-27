<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Filesystem;

use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Filesystem\Contracts\Cloud as CloudContract;
use SwooleTW\Hyperf\Filesystem\Contracts\Factory as FactoryContract;

class CloudStorageFactory
{
    public function __invoke(ContainerInterface $container): CloudContract
    {
        return $container->get(FactoryContract::class)
            ->cloud(CloudContract::class);
    }
}
