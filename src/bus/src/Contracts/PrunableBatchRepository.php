<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Bus\Contracts;

use DateTimeInterface;

interface PrunableBatchRepository extends BatchRepository
{
    /**
     * Prune all of the entries older than the given date.
     */
    public function prune(DateTimeInterface $before): int;
}
