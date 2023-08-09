<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth;

use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Auth\Contracts\FactoryContract;

class UserResolver
{
    public function __invoke(ContainerInterface $container): array
    {
        return $container->get(FactoryContract::class)
            ->userResolver();
    }
}
