<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Broadcasting;

use Psr\EventDispatcher\EventDispatcherInterface;

class PendingBroadcast
{
    /**
     * The event dispatcher implementation.
     */
    protected EventDispatcherInterface $events;

    /**
     * The event instance.
     */
    protected mixed $event;

    /**
     * Create a new pending broadcast instance.
     */
    public function __construct(EventDispatcherInterface $events, mixed $event)
    {
        $this->event = $event;
        $this->events = $events;
    }

    /**
     * Broadcast the event using a specific broadcaster.
     */
    public function via(?string $connection = null): static
    {
        if (method_exists($this->event, 'broadcastVia')) {
            $this->event->broadcastVia($connection);
        }

        return $this;
    }

    /**
     * Broadcast the event to everyone except the current user.
     */
    public function toOthers(): static
    {
        if (method_exists($this->event, 'dontBroadcastToCurrentUser')) {
            $this->event->dontBroadcastToCurrentUser();
        }

        return $this;
    }

    /**
     * Handle the object's destruction.
     */
    public function __destruct()
    {
        $this->events->dispatch($this->event);
    }
}
