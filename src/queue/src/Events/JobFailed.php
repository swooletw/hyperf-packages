<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Queue\Events;

use SwooleTW\Hyperf\Queue\Contracts\Job;
use Throwable;

class JobFailed
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $connectionName,
        public Job $job,
        public Throwable $exception
    ) {
    }
}
