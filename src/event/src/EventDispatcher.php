<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Event;

use Closure;
use Exception;
use Hyperf\AsyncQueue\Driver\DriverFactory as QueueFactory;
use Hyperf\Collection\Arr;
use Hyperf\Context\ApplicationContext;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use SwooleTW\Hyperf\Event\Contract\ListenerProviderInterface;
use SwooleTW\Hyperf\Foundation\Contracts\Queue\ShouldQueue;
use SwooleTW\Hyperf\Support\Traits\ReflectsClosures;

class EventDispatcher implements EventDispatcherInterface
{
    use ReflectsClosures;

    /** @var callable */
    protected $queueResolver;

    public function __construct(
        protected ListenerProviderInterface $listeners,
        protected ?LoggerInterface $logger = null,
        protected ?ContainerInterface $container = null
    ) {
        if (! $container && ApplicationContext::hasContainer()) {
            $this->container = ApplicationContext::getContainer();
        }
    }

    public function dispatch(object|string $event, mixed $payload = [], bool $halt = false): object|string
    {
        return $this->invokeListeners($event, $payload, $halt);
    }

    protected function dump(mixed $listener, object|string $event)
    {
        if (! $this->logger) {
            return;
        }

        $eventName = is_string($event) ? $event : get_class($event);
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

    public function listen(
        array|Closure|QueuedClosure|string $events,
        null|array|Closure|int|QueuedClosure|string $listener = null,
        int $priority = ListenerData::DEFAULT_PRIORITY
    ): void {
        if ($events instanceof Closure) {
            foreach ((array) $this->firstClosureParameterTypes($events) as $event) {
                $this->listeners->on($event, $events, is_int($listener) ? $listener : $priority);
            }

            return;
        }

        if ($events instanceof QueuedClosure) {
            foreach ((array) $this->firstClosureParameterTypes($events->closure) as $event) {
                $this->listeners->on($event, $events->resolve($this->queueResolver), is_int($listener) ? $listener : $priority);
            }

            return;
        }

        if ($listener instanceof QueuedClosure) {
            $listener = $listener->resolve($this->queueResolver);
        }

        foreach ((array) $events as $event) {
            $this->listeners->on($event, $listener, $priority);
        }
    }

    public function until(object|string $event, mixed $payload = []): object|string
    {
        return $this->dispatch($event, $payload, true);
    }

    protected function invokeListeners(object|string $event, mixed $payload, bool $halt = false): object|string
    {
        foreach ($this->getListeners($event) as $listener) {
            $response = $listener($event, $payload);

            $this->dump($listener, $event);

            if ($halt || $response === false || ($event instanceof StoppableEventInterface && $event->isPropagationStopped())) {
                return $event;
            }
        }

        return $event;
    }

    public function getListeners(object|string $eventName): iterable
    {
        return $this->prepareListeners($eventName);
    }

    /**
     * @return Closure[]
     */
    protected function prepareListeners(object|string $eventName): array
    {
        $listeners = [];

        foreach ($this->listeners->getListenersForEvent($eventName) as $listener) {
            $listeners[] = $this->makeListener($listener);
        }

        return $listeners;
    }

    protected function makeListener(array|Closure|string $listener): Closure
    {
        if (is_string($listener) || (is_array($listener) && ((isset($listener[0]) && is_string($listener[0])) || is_callable($listener)))) {
            return $this->createClassListener($listener);
        }

        return function ($event, $payload) use ($listener) {
            if (is_array($payload)) {
                return $listener($event, ...array_values($payload));
            }

            return $listener($event, $payload);
        };
    }

    protected function createClassListener(array|string $listener): Closure
    {
        return function (object|string $event, mixed $payload) use ($listener) {
            $callable = $this->createClassCallable($listener);

            if (is_array($payload)) {
                return $callable($event, ...array_values($payload));
            }

            return $callable($event, $payload);
        };
    }

    protected function createClassCallable(array|string $listener): callable
    {
        [$class, $method] = is_array($listener)
                            ? $listener
                            : $this->parseClassCallable($listener);

        if (! method_exists($class, $method)) {
            $method = '__invoke';
        }

        if ($this->handlerShouldBeQueued($class)) {
            return $this->createQueuedHandlerCallable($class, $method);
        }

        $listener = is_string($class) ? $this->container->get($class) : $class;

        return [$listener, $method];
    }

    protected function parseClassCallable(string $listener): array
    {
        return Str::parseCallback($listener, 'handle');
    }

    public function push(string $event, mixed $payload = []): void
    {
        $this->listen($event . '_pushed', function () use ($event, $payload) {
            $this->dispatch($event, $payload);
        });
    }

    public function flush(string $event): void
    {
        $this->dispatch($event . '_pushed');
    }

    public function forgetPushed(): void
    {
        foreach ($this->listeners->all() as $key => $_) {
            if (str_ends_with($key, '_pushed')) {
                $this->forget($key);
            }
        }
    }

    public function forget(string $event): void
    {
        $this->listeners->forget($event);
    }

    public function hasListeners(string $eventName): bool
    {
        return $this->listeners->has($eventName);
    }

    public function hasWildcardListeners(string $eventName): bool
    {
        return $this->listeners->hasWildcard($eventName);
    }

    protected function resolveQueue(): QueueFactory
    {
        return call_user_func($this->queueResolver);
    }

    public function setQueueResolver(callable $resolver): static
    {
        $this->queueResolver = $resolver;

        return $this;
    }

    protected function handlerShouldBeQueued(object|string $class): bool
    {
        try {
            if (is_string($class)) {
                return (new ReflectionClass($class))->implementsInterface(ShouldQueue::class);
            }

            return $class instanceof ShouldQueue;
        } catch (Exception) {
            return false;
        }
    }

    protected function createQueuedHandlerCallable(object|string $class, string $method): Closure
    {
        return function () use ($class, $method) {
            $arguments = array_map(function ($a) {
                return is_object($a) ? clone $a : $a;
            }, func_get_args());

            if ($this->handlerWantsToBeQueued($class, $arguments)) {
                $this->queueHandler($class, $method, $arguments);
            }
        };
    }

    protected function handlerWantsToBeQueued(object|string $class, array $arguments): bool
    {
        $instance = is_string($class) ? $this->container->get($class) : $class;

        if (method_exists($instance, 'shouldQueue')) {
            return $instance->shouldQueue($arguments[0]);
        }

        return true;
    }

    protected function queueHandler(object|string $class, string $method, array $arguments): void
    {
        [$listener, $job] = $this->createListenerAndJob($class, $method, $arguments);

        $connection = $this->resolveQueue()->get(method_exists($listener, 'viaConnection')
            ? $listener->viaConnection(...$arguments)
            : $listener->connection ?? 'default');

        $delay = method_exists($listener, 'withDelay')
            ? $listener->withDelay(...$arguments)
            : $listener->delay ?? 0;

        $connection->push($job, $delay);
    }

    protected function createListenerAndJob(object|string $class, string $method, array $arguments): array
    {
        $listener = is_string($class) ? (new ReflectionClass($class))->newInstanceWithoutConstructor() : $class;

        return [$listener, $this->propagateListenerOptions(
            $listener,
            new CallQueuedListener($class, $method, $arguments)
        )];
    }

    protected function propagateListenerOptions(mixed $listener, CallQueuedListener $job): mixed
    {
        return tap($job, function ($job) use ($listener) {
            $job->setMaxAttempts($listener->maxAttempts ?? 0);
        });
    }

    public function subscribe(object|string $subscriber): void
    {
        $subscriber = $this->resolveSubscriber($subscriber);

        $events = $subscriber->subscribe($this);

        if (is_array($events)) {
            foreach ($events as $event => $listeners) {
                foreach (Arr::wrap($listeners) as $listener) {
                    if (is_string($listener) && method_exists($subscriber, $listener)) {
                        $this->listen($event, [get_class($subscriber), $listener]);

                        continue;
                    }

                    $this->listen($event, $listener);
                }
            }
        }
    }

    protected function resolveSubscriber(object|string $subscriber): mixed
    {
        if (is_string($subscriber)) {
            return $this->container->get($subscriber);
        }

        return $subscriber;
    }
}
