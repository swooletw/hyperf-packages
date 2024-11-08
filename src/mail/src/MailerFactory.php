<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Mail;

use SwooleTW\Hyperf\Mail\Contracts\Factory;
use SwooleTW\Hyperf\Mail\Contracts\Mailer as MailerContract;

class MailerFactory
{
    public function __construct(
        protected Factory $manager
    ) {
    }

    public function __invoke(): MailerContract
    {
        return $this->manager->mailer();
    }
}
