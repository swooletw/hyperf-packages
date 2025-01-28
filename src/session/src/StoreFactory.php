<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Session;

use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Session\Contracts\Factory;
use SwooleTW\Hyperf\Session\Contracts\Session as SessionContract;

class StoreFactory
{
    public function __invoke(ContainerInterface $container): SessionContract
    {
        return $container->get(Factory::class)
            ->driver();
    }
}
