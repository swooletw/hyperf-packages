<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Mail\Contracts;

use DateInterval;
use DateTimeInterface;
use SwooleTW\Hyperf\Mail\Contracts\Mailable as MailableContract;

interface MailQueue
{
    /**
     * Queue a new e-mail message for sending.
     */
    public function queue(array|MailableContract|string $view, ?string $queue = null): mixed;

    /**
     * Queue a new e-mail message for sending after (n) seconds.
     */
    public function later(DateInterval|DateTimeInterface|int $delay, array|MailableContract|string $view, ?string $queue = null): mixed;
}
