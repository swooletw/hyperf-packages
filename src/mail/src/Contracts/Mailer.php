<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Mail\Contracts;

use Closure;
use SwooleTW\Hyperf\Mail\PendingMail;
use SwooleTW\Hyperf\Mail\SentMessage;

interface Mailer
{
    /**
     * Begin the process of mailing a mailable class instance.
     */
    public function to(mixed $users): PendingMail;

    /**
     * Begin the process of mailing a mailable class instance.
     */
    public function bcc(mixed $users): PendingMail;

    /**
     * Send a new message with only a raw text part.
     */
    public function raw(string $text, mixed $callback): ?SentMessage;

    /**
     * Send a new message using a view.
     */
    public function send(array|Mailable|string $view, array $data = [], null|Closure|string $callback = null): ?SentMessage;
}
