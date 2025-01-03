<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Testing\Fakes;

use Closure;
use SwooleTW\Hyperf\Bus\PendingChain;
use SwooleTW\Hyperf\Bus\PendingDispatch;
use SwooleTW\Hyperf\Queue\CallQueuedClosure;

class PendingChainFake extends PendingChain
{
    /**
     * Create a new pending chain instance.
     *
     * @param BusFake $bus the fake bus instance
     */
    public function __construct(
        public BusFake $bus,
        public mixed $job,
        public array $chain = []
    ) {
    }

    /**
     * Dispatch the job with the given arguments.
     */
    public function dispatch(): PendingDispatch
    {
        if (is_string($this->job)) {
            $firstJob = new $this->job(...func_get_args());
        } elseif ($this->job instanceof Closure) {
            $firstJob = CallQueuedClosure::create($this->job);
        } else {
            $firstJob = $this->job;
        }

        $firstJob->allOnConnection($this->connection);
        $firstJob->allOnQueue($this->queue);
        $firstJob->chain($this->chain);
        $firstJob->delay($this->delay);
        $firstJob->chainCatchCallbacks = $this->catchCallbacks();

        return $this->bus->dispatch($firstJob);
    }
}
