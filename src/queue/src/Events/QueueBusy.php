<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Queue\Events;

class QueueBusy
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $connection,
        public string $queue,
        public int $size
    ) {
    }
}
