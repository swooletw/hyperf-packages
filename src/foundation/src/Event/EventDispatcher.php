<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Event;

use Closure;
use Hyperf\AsyncQueue\Driver\DriverFactory as HyperfQueueDriverFactory;
use Hyperf\Context\ApplicationContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Log\LoggerInterface;
use SwooleTW\Hyperf\Foundation\Contracts\Queue\Queueable;

class EventDispatcher implements EventDispatcherInterface
{
    protected ?HyperfQueueDriverFactory $queueFactory = null;

    public function __construct(
        protected ListenerProviderInterface $listeners,
        protected ?LoggerInterface $logger = null
    ) {}

    /**
     * Provide all listeners with an event to process.
     *
     * @param object $event The object to process
     * @return object The Event that was passed, now modified by listeners
     */
    public function dispatch(object $event)
    {
        foreach ($this->listeners->getListenersForEvent($event) as $listener) {
            $listenerInstance = $listener instanceof Closure
                ? $listener
                : ($listener[0] ?? null);
            // push queueable listener to queue
            if ($listenerInstance instanceof Queueable) {
                $listener = new QueableListener($event, get_class($listenerInstance));
                $this->getQueueFactory()
                    ->get($listener->queue ?? 'default')
                    ->push($listener, $listener->delay ?? 0);
                $this->dumpQueableListener($listener, $event);

                if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                    break;
                }
                continue;
            }

            $listener($event);
            $this->dump($listener, $event);
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
        }
        return $event;
    }

    protected function getQueueFactory(): HyperfQueueDriverFactory
    {
        if ($factory = $this->queueFactory) {
            return $factory;
        }

        return $this->queueFactory = ApplicationContext::getContainer()
            ->get(HyperfQueueDriverFactory::class);
    }

    /**
     * Dump the debug message if $logger property is provided.
     * @param mixed $listener
     */
    private function dump($listener, object $event)
    {
        if (! $this->logger) {
            return;
        }
        $eventName = get_class($event);
        $listenerName = '[ERROR TYPE]';
        if (is_array($listener)) {
            $listenerName = is_string($listener[0]) ? $listener[0] : get_class($listener[0]);
        } elseif (is_string($listener)) {
            $listenerName = $listener;
        } elseif (is_object($listener)) {
            $listenerName = get_class($listener);
        }
        $this->logger->debug(sprintf('Event %s handled by %s listener.', $eventName, $listenerName));
    }

    protected function dumpQueableListener(QueableListener $listener, object $event): void
    {
        if (! $this->logger) {
            return;
        }

        $eventName = get_class($event);
        $queueName = $listener->queue ?? 'default';

        $this->logger->debug("Listener was pushed to queue `{$queueName}` by event `{$eventName}`.");
    }
}
