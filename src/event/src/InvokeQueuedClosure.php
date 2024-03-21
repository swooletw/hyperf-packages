<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Event;

use Laravel\SerializableClosure\SerializableClosure;
use Throwable;

class InvokeQueuedClosure
{
    /**
     * Handle the event.
     */
    public function handle(SerializableClosure $closure, array $arguments): void
    {
        call_user_func($closure->getClosure(), ...$arguments);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $e, SerializableClosure $closure, array $arguments, array $catchCallbacks): void
    {
        collect($catchCallbacks)->each->__invoke(...[$e, ...$arguments]);
    }
}
