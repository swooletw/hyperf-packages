<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Queue\Failed;

use DateTimeInterface;

interface PrunableFailedJobProvider
{
    /**
     * Prune all of the entries older than the given date.
     */
    public function prune(DateTimeInterface $before): int;
}
