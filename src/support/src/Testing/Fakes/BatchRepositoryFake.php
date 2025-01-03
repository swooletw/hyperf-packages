<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Testing\Fakes;

use Carbon\CarbonImmutable;
use Closure;
use Hyperf\Stringable\Str;
use SwooleTW\Hyperf\Bus\Batch;
use SwooleTW\Hyperf\Bus\Contracts\BatchRepository;
use SwooleTW\Hyperf\Bus\PendingBatch;
use SwooleTW\Hyperf\Bus\UpdatedBatchJobCounts;
use SwooleTW\Hyperf\Support\Carbon;

class BatchRepositoryFake implements BatchRepository
{
    /**
     * The batches stored in the repository.
     *
     * @var \SwooleTW\Hyperf\Bus\Batch[]
     */
    protected array $batches = [];

    /**
     * Retrieve a list of batches.
     *
     * @return \SwooleTW\Hyperf\Bus\Batch[]
     */
    public function get(int $limit, mixed $before): array
    {
        return $this->batches;
    }

    /**
     * Retrieve information about an existing batch.
     */
    public function find(int|string $batchId): ?Batch
    {
        return $this->batches[$batchId] ?? null;
    }

    /**
     * Store a new pending batch.
     */
    public function store(PendingBatch $batch): Batch
    {
        $id = (string) Str::orderedUuid();

        $this->batches[$id] = new BatchFake(
            $id,
            $batch->name,
            count($batch->jobs),
            count($batch->jobs),
            0,
            [],
            $batch->options,
            CarbonImmutable::now(),
            null,
            null
        );

        return $this->batches[$id];
    }

    /**
     * Increment the total number of jobs within the batch.
     */
    public function incrementTotalJobs(int|string $batchId, int $amount): void
    {
    }

    /**
     * Decrement the total number of pending jobs for the batch.
     */
    public function decrementPendingJobs(int|string $batchId, string $jobId): UpdatedBatchJobCounts
    {
        return new UpdatedBatchJobCounts();
    }

    /**
     * Increment the total number of failed jobs for the batch.
     */
    public function incrementFailedJobs(int|string $batchId, string $jobId): UpdatedBatchJobCounts
    {
        return new UpdatedBatchJobCounts();
    }

    /**
     * Mark the batch that has the given ID as finished.
     */
    public function markAsFinished(int|string $batchId): void
    {
        if (isset($this->batches[$batchId])) {
            $this->batches[$batchId]->finishedAt = Carbon::now();
        }
    }

    /**
     * Cancel the batch that has the given ID.
     */
    public function cancel(int|string $batchId): void
    {
        if (isset($this->batches[$batchId])) {
            $this->batches[$batchId]->cancel();
        }
    }

    /**
     * Delete the batch that has the given ID.
     */
    public function delete(int|string $batchId): void
    {
        unset($this->batches[$batchId]);
    }

    /**
     * Execute the given Closure within a storage specific transaction.
     */
    public function transaction(Closure $callback): mixed
    {
        return $callback();
    }

    /**
     * Rollback the last database transaction for the connection.
     */
    public function rollBack(): void
    {
    }
}
