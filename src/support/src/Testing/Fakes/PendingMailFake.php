<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Testing\Fakes;

use SwooleTW\Hyperf\Mail\Contracts\Mailable;
use SwooleTW\Hyperf\Mail\PendingMail;
use SwooleTW\Hyperf\Mail\SentMessage;

class PendingMailFake extends PendingMail
{
    /**
     * Send a new mailable message instance.
     */
    public function send(Mailable $mailable): ?SentMessage
    {
        $this->mailer->send($this->fill($mailable));

        return null;
    }

    /**
     * Send a new mailable message instance synchronously.
     */
    public function sendNow(Mailable $mailable): ?SentMessage
    {
        return $this->send($mailable);
    }
}
