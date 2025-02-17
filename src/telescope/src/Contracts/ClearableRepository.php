<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Telescope\Contracts;

interface ClearableRepository
{
    /**
     * Clear all of the entries.
     */
    public function clear(): void;
}
