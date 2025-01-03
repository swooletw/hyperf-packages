<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Bus\Contracts;

use Hyperf\Collection\Collection;
use SwooleTW\Hyperf\Bus\Batch;
use SwooleTW\Hyperf\Bus\PendingBatch;

interface QueueingDispatcher extends Dispatcher
{
    /**
     * Attempt to find the batch with the given ID.
     */
    public function findBatch(string $batchId): ?Batch;

    /**
     * Create a new batch of queueable jobs.
     */
    public function batch(array|Collection $jobs): PendingBatch;

    /**
     * Dispatch a command to its appropriate handler behind a queue.
     */
    public function dispatchToQueue(mixed $command): mixed;
}
