<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache\RateLimiting;

class GlobalLimit extends Limit
{
    /**
     * Create a new limit instance.
     */
    public function __construct(int $maxAttempts, int $decayMinutes = 1)
    {
        parent::__construct('', $maxAttempts, $decayMinutes);
    }
}
