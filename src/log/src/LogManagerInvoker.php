<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Log;

use Psr\Container\ContainerInterface;

class LogManagerInvoker
{
    public function __invoke(ContainerInterface $container)
    {
        return $container->get(LogManager::class);
    }
}
