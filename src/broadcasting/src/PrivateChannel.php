<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Broadcasting;

use SwooleTW\Hyperf\Broadcasting\Contracts\HasBroadcastChannel;

class PrivateChannel extends Channel
{
    /**
     * Create a new channel instance.
     */
    public function __construct(HasBroadcastChannel|string $name)
    {
        $name = $name instanceof HasBroadcastChannel ? $name->broadcastChannel() : $name;

        parent::__construct('private-' . $name);
    }
}
