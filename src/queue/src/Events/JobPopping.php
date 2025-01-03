<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Queue\Events;

class JobPopping
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $connectionName
    ) {
    }
}
