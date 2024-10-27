<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Filesystem;

use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Filesystem\Contracts\Factory as FactoryContract;
use SwooleTW\Hyperf\Filesystem\Contracts\Filesystem as FilesystemContract;

class FilesystemFactory
{
    public function __invoke(ContainerInterface $container): FilesystemContract
    {
        return $container->get(FactoryContract::class)
            ->disk();
    }
}
