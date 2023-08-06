<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Log\Adapter;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Log\Adapter\LogFactoryAdapter;

class HyperfLogFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new LogFactoryAdapter(
            $container,
            $container->get(ConfigInterface::class)
        );
    }
}
