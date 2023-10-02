<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth\Contracts;

interface Authorizable
{
    /**
     * Determine if the entity has a given ability.
     */
    public function can(iterable|string $abilities, mixed $arguments = []): bool;
}
