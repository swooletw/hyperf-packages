<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Queue\Contracts;

interface QueueableEntity
{
    /**
     * Get the queueable identity for the entity.
     */
    public function getQueueableId(): mixed;

    /**
     * Get the relationships for the entity.
     */
    public function getQueueableRelations(): array;

    /**
     * Get the connection of the entity.
     */
    public function getQueueableConnection(): ?string;
}
