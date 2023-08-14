<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\JWT\Blacklist;
use SwooleTW\Hyperf\JWT\Contracts\BlacklistContract;

class BlacklistFactory
{
    public function __invoke(ContainerInterface $container): BlacklistContract
    {
        $config = $container->get(ConfigInterface::class);

        return new Blacklist(
            $container->get($config->get('jwt.providers.storage')),
            (int) $config->get('jwt.blacklist_grace_period', 0),
            (int) $config->get('jwt.blacklist_refresh_ttl', 20160)
        );
    }
}
