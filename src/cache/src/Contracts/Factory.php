<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache\Contracts;

interface Factory
{
    /**
     * Get a cache store instance by name.
     */
    public function store(?string $name = null): Repository;
}
