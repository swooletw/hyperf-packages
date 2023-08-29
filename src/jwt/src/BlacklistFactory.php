<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Cache\Contracts\Repository as CacheContract;
use SwooleTW\Hyperf\JWT\Contracts\BlacklistContract;
use SwooleTW\Hyperf\JWT\Storage\TaggedCache;

class BlacklistFactory
{
    public function __invoke(ContainerInterface $container): BlacklistContract
    {
        $config = $container->get(ConfigInterface::class);

        $storageClass = $config->get('jwt.providers.storage');
        $storage = match ($storageClass) {
            TaggedCache::class => new TaggedCache($container->get(CacheContract::class)->driver()),
            default => $container->get($storageClass),
        };

        return new Blacklist(
            $storage,
            (int) $config->get('jwt.blacklist_grace_period', 0),
            (int) $config->get('jwt.blacklist_refresh_ttl', 20160)
        );
    }
}
