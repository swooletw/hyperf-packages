<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Broadcasting;

use Stringable;
use SwooleTW\Hyperf\Broadcasting\Contracts\HasBroadcastChannel;

class Channel implements Stringable
{
    /**
     * The channel's name.
     */
    public string $name;

    /**
     * Create a new channel instance.
     */
    public function __construct(HasBroadcastChannel|string $name)
    {
        $this->name = $name instanceof HasBroadcastChannel ? $name->broadcastChannel() : $name;
    }

    /**
     * Convert the channel instance to a string.
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
