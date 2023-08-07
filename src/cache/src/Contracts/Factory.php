<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache\Contracts;

interface Factory
{
    /**
     * Get a cache store instance by name.
     *
     * @param null|string $name
     * @return \SwooleTW\Hyperf\Cache\Contracts\Repository
     */
    public function store($name = null);
}
