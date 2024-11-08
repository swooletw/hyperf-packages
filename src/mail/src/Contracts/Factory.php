<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Mail\Contracts;

interface Factory
{
    /**
     * Get a mailer instance by name.
     */
    public function mailer(?string $name = null): Mailer;
}
