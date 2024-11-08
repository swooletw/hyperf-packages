<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Mail\Events;

use Symfony\Component\Mime\Email;

class MessageSending
{
    public bool $shouldSend = true;

    /**
     * Create a new event instance.
     */
    public function __construct(
        protected Email $message,
        protected array $data = []
    ) {
    }

    /**
     * Determine if the message should be sent.
     */
    public function shouldSend(): bool
    {
        return $this->shouldSend;
    }
}
