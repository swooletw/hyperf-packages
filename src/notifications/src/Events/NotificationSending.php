<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Notifications\Events;

use SwooleTW\Hyperf\Notifications\Notification;

class NotificationSending
{
    public bool $shouldSend = true;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public mixed $notifiable,
        public Notification $notification,
        public string $channel
    ) {
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(): bool
    {
        return $this->shouldSend;
    }
}
