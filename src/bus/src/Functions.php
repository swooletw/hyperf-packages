<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Bus;

use Closure;
use Hyperf\Context\ApplicationContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Bus\Contracts\Dispatcher;
use SwooleTW\Hyperf\Queue\CallQueuedClosure;

/**
 * Dispatch a job to its appropriate handler.
 *
 * @param mixed $job
 * @return ($job is Closure ? PendingClosureDispatch : PendingDispatch)
 */
function dispatch($job): PendingClosureDispatch|PendingDispatch
{
    return $job instanceof Closure
        ? new PendingClosureDispatch(CallQueuedClosure::create($job))
        : new PendingDispatch($job);
}

/**
 * Dispatch a command to its appropriate handler in the current process.
 *
 * Queueable jobs will be dispatched to the "sync" queue.
 */
function dispatch_sync(mixed $job, mixed $handler = null): mixed
{
    return ApplicationContext::getContainer()
        ->get(Dispatcher::class)
        ->dispatchSync($job, $handler);
}

/**
 * Dispatch an event and call the listeners.
 *
 * @template T of object
 *
 * @param T $event
 *
 * @return T
 */
function event(object $event)
{
    return ApplicationContext::getContainer()
        ->get(EventDispatcherInterface::class)
        ->dispatch($event);
}
