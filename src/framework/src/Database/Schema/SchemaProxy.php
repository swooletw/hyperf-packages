<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Database\Schema;

use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Schema\Builder;
use SwooleTW\Hyperf\Foundation\ApplicationContext;

/**
 * @mixin Builder
 */
class SchemaProxy
{
    public function __call(string $name, array $arguments)
    {
        return ApplicationContext::getContainer()
            ->get(ConnectionResolverInterface::class)
            ->connection()
            ->getSchemaBuilder()
            ->{$name}(...$arguments);
    }

    /**
     * Create a connection by ConnectionResolver.
     */
    public function connection(?string $name = null): ConnectionInterface
    {
        $resolver = ApplicationContext::getContainer()
            ->get(ConnectionResolverInterface::class);

        return $resolver->connection(
            $name ?: $resolver->getDefaultConnection()
        );
    }
}
