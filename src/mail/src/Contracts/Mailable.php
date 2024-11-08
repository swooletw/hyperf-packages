<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Mail\Contracts;

use SwooleTW\Hyperf\Mail\SentMessage;

interface Mailable
{
    /**
     * Send the message using the given mailer.
     */
    public function send(Factory|Mailer $mailer): ?SentMessage;

    /**
     * Set the recipients of the message.
     */
    public function cc(array|object|string $address, ?string $name = null): static;

    /**
     * Set the recipients of the message.
     */
    public function bcc(array|object|string $address, ?string $name = null): static;

    /**
     * Set the recipients of the message.
     */
    public function to(array|object|string $address, ?string $name = null): static;

    /**
     * Set the locale of the message.
     */
    public function locale(string $locale): static;

    /**
     * Set the name of the mailer that should be used to send the message.
     */
    public function mailer(string $mailer): static;
}
