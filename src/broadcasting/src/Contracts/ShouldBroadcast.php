<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Broadcasting\Contracts;

use SwooleTW\Hyperf\Broadcasting\Channel;

interface ShouldBroadcast
{
    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|Channel[]|string|string[]
     */
    public function broadcastOn(): array|Channel|string;
}
