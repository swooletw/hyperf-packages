<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth\Access;

use Hyperf\Contract\ContainerInterface;
use SwooleTW\Hyperf\Auth\Contracts\FactoryContract;

use function Hyperf\Support\make;

class GateFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $userResolver = $container->get(FactoryContract::class)->userResolver();

        return make(Gate::class, compact('container', 'userResolver'));
    }
}
