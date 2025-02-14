<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Telescope\Contracts;

interface TerminableRepository
{
    /**
     * Perform any clean-up tasks needed after storing Telescope entries.
     */
    public function terminate(): void;
}
