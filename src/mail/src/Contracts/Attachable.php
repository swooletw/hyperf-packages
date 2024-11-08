<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Mail\Contracts;

use SwooleTW\Hyperf\Mail\Attachment;

interface Attachable
{
    /**
     * Get an attachment instance for this entity.
     */
    public function toMailAttachment(): Attachment;
}
