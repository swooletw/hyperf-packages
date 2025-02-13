<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Session;

use Hyperf\Contract\SessionInterface;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Session\Contracts\Session as SessionContract;

class AdapterFactory
{
    public function __invoke(ContainerInterface $container): SessionInterface
    {
        return new SessionAdapter(
            $container->get(SessionContract::class)
        );
    }
}
