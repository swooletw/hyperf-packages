<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Broadcasting;

use Hyperf\Collection\Arr;
use Hyperf\Contract\Arrayable;
use ReflectionClass;
use ReflectionProperty;
use SwooleTW\Hyperf\Broadcasting\Contracts\Factory as BroadcastingFactory;

// TODO: 當 queue 移植過來後補上
// class BroadcastEvent implements ShouldQueue
class BroadcastEvent
{
    // TODO: 當 queue 移植過來後補上
    // use Queueable;

    /**
     * The event instance.
     */
    public mixed $event;

    /**
     * The number of times the job may be attempted.
     */
    public ?int $tries;

    /**
     * The number of seconds the job can run before timing out.
     */
    public ?int $timeout;

    /**
     * The number of seconds to wait before retrying the job when encountering an uncaught exception.
     */
    public ?int $backoff;

    /**
     * Indicate that the event should be dispatched after all open database transactions have been committed.
     */
    public ?bool $afterCommit;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public ?int $maxExceptions;

    /**
     * Create a new job handler instance.
     */
    public function __construct(mixed $event)
    {
        $this->event = $event;
        $this->tries = $event->tries ?? null;
        $this->timeout = $event->timeout ?? null;
        $this->backoff = $event->backoff ?? null;
        $this->afterCommit = $event->afterCommit ?? null;
        $this->maxExceptions = $event->maxExceptions ?? null;
    }

    /**
     * Handle the queued job.
     */
    public function handle(BroadcastingFactory $manager): void
    {
        $channels = Arr::wrap($this->event->broadcastOn());

        if (empty($channels)) {
            return;
        }

        $name = method_exists($this->event, 'broadcastAs')
            ? $this->event->broadcastAs()
            : get_class($this->event);

        $connections = method_exists($this->event, 'broadcastConnections')
            ? $this->event->broadcastConnections()
            : [null];

        $payload = $this->getPayloadFromEvent($this->event);

        foreach ($connections as $connection) {
            $manager->connection($connection)->broadcast(
                $channels,
                $name,
                $payload
            );
        }
    }

    /**
     * Get the payload for the given event.
     */
    protected function getPayloadFromEvent(mixed $event): array
    {
        if (method_exists($event, 'broadcastWith')
            && ! is_null($payload = $event->broadcastWith())
        ) {
            return array_merge($payload, ['socket' => data_get($event, 'socket')]);
        }

        $payload = [];

        foreach ((new ReflectionClass($event))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $payload[$property->getName()] = $this->formatProperty($property->getValue($event));
        }

        unset($payload['broadcastQueue']);

        return $payload;
    }

    /**
     * Format the given value for a property.
     */
    protected function formatProperty(mixed $value): mixed
    {
        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        return $value;
    }

    /**
     * Get the display name for the queued job.
     */
    public function displayName(): string
    {
        return get_class($this->event);
    }

    /**
     * Prepare the instance for cloning.
     */
    public function __clone()
    {
        $this->event = clone $this->event;
    }
}
