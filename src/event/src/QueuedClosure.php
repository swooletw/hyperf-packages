<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Event;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;

class QueuedClosure
{
    /**
     * The name of the connection the job should be sent to.
     */
    public ?string $connection;

    /**
     * The number of seconds before the job should be made available.
     */
    public null|int $delay;

    /**
     * All of the "catch" callbacks for the queued closure.
     */
    public array $catchCallbacks = [];

    /**
     * Create a new queued closure event listener resolver.
     *
     * @param Closure $closure The underlying Closure
     */
    public function __construct(public Closure $closure) {}

    /**
     * Set the desired connection for the job.
     */
    public function onConnection(?string $connection): static
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Set the desired delay in seconds for the job.
     */
    public function delay(null|int $delay): static
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * Specify a callback that should be invoked if the queued listener job fails.
     */
    public function catch(Closure $closure): static
    {
        $this->catchCallbacks[] = $closure;

        return $this;
    }

    /**
     * Resolve the actual event listener callback.
     */
    public function resolve(callable $queueResolver): Closure
    {
        return function (...$arguments) use ($queueResolver) {
            $job = new CallQueuedListener(InvokeQueuedClosure::class, 'handle', [
                'closure' => new SerializableClosure($this->closure),
                'arguments' => $arguments,
                'catch' => collect($this->catchCallbacks)->map(function ($callback) {
                    return new SerializableClosure($callback);
                })->all(),
            ]);

            $connection = $queueResolver()->get($this->connection ?? 'default');
            $delay = $this->delay ?? 0;

            $connection->push($job, $delay);
        };
    }
}
