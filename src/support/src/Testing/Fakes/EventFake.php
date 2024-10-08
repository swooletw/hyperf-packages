<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Testing\Fakes;

use Closure;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Stringable\Str;
use PHPUnit\Framework\Assert as PHPUnit;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionFunction;
use SwooleTW\Hyperf\Support\Traits\ReflectsClosures;

class EventFake implements EventDispatcherInterface
{
    use ReflectsClosures;

    /**
     * The original event dispatcher.
     */
    protected EventDispatcherInterface $dispatcher;

    /**
     * The event types that should be intercepted instead of dispatched.
     */
    protected array $eventsToFake = [];

    /**
     * All of the events that have been intercepted keyed by type.
     */
    protected array $events = [];

    /**
     * Create a new event fake instance.
     *
     * @param array|string $eventsToFake
     */
    public function __construct(EventDispatcherInterface $dispatcher, $eventsToFake = [])
    {
        $this->dispatcher = $dispatcher;
        $this->eventsToFake = Arr::wrap($eventsToFake);
    }

    /**
     * Assert if an event has a listener attached to it.
     *
     * @param string $expectedEvent
     * @param string $expectedListener
     */
    public function assertListening($expectedEvent, $expectedListener)
    {
        /* @phpstan-ignore-next-line */
        foreach ($this->dispatcher->getListeners($expectedEvent) as $listenerClosure) {
            $actualListener = (new ReflectionFunction($listenerClosure))
                ->getStaticVariables()['listener'];

            if (is_string($actualListener) && Str::endsWith($actualListener, '@handle')) {
                $actualListener = Str::parseCallback($actualListener)[0];
            }

            if ($actualListener === $expectedListener
                || ($actualListener instanceof Closure
                && $expectedListener === Closure::class)) {
                PHPUnit::assertTrue(true);

                return;
            }
        }

        PHPUnit::assertTrue(
            false,
            sprintf(
                'Event [%s] does not have the [%s] listener attached to it',
                $expectedEvent,
                print_r($expectedListener, true)
            )
        );
    }

    /**
     * Assert if an event was dispatched based on a truth-test callback.
     *
     * @param Closure|string $event
     * @param null|callable|int $callback
     */
    public function assertDispatched($event, $callback = null)
    {
        if ($event instanceof Closure) {
            [$event, $callback] = [$this->firstClosureParameterType($event), $event];
        }

        if (is_int($callback)) {
            $this->assertDispatchedTimes($event, $callback);
            return;
        }

        PHPUnit::assertTrue(
            $this->dispatched($event, $callback)->count() > 0,
            "The expected [{$event}] event was not dispatched."
        );
    }

    /**
     * Assert if an event was dispatched a number of times.
     *
     * @param string $event
     * @param int $times
     */
    public function assertDispatchedTimes($event, $times = 1)
    {
        $count = $this->dispatched($event)->count();

        PHPUnit::assertSame(
            $times,
            $count,
            "The expected [{$event}] event was dispatched {$count} times instead of {$times} times."
        );
    }

    /**
     * Determine if an event was dispatched based on a truth-test callback.
     *
     * @param Closure|string $event
     * @param null|callable $callback
     */
    public function assertNotDispatched($event, $callback = null)
    {
        if ($event instanceof Closure) {
            [$event, $callback] = [$this->firstClosureParameterType($event), $event];
        }

        PHPUnit::assertCount(
            0,
            $this->dispatched($event, $callback),
            "The unexpected [{$event}] event was dispatched."
        );
    }

    /**
     * Assert that no events were dispatched.
     */
    public function assertNothingDispatched()
    {
        $count = count(Arr::flatten($this->events));

        PHPUnit::assertSame(
            0,
            $count,
            "{$count} unexpected events were dispatched."
        );
    }

    /**
     * Get all of the events matching a truth-test callback.
     *
     * @param string $event
     * @param null|callable $callback
     * @return \Hyperf\Collection\Collection
     */
    public function dispatched($event, $callback = null)
    {
        if (! $this->hasDispatched($event)) {
            return Collection::make();
        }

        $callback = $callback ?: function () {
            return true;
        };

        return Collection::make($this->events[$event])->filter(function ($arguments) use ($callback) {
            return $callback(...$arguments);
        });
    }

    /**
     * Determine if the given event has been dispatched.
     *
     * @param string $event
     * @return bool
     */
    public function hasDispatched($event)
    {
        return isset($this->events[$event]) && ! empty($this->events[$event]);
    }

    /**
     * Fire an event and call the listeners.
     *
     * @param object|string $event
     * @param mixed $payload
     * @param bool $halt
     */
    public function dispatch($event, $payload = [], $halt = false)
    {
        $name = is_object($event) ? get_class($event) : (string) $event;

        if ($this->shouldFakeEvent($name, $payload)) {
            $this->events[$name][] = func_get_args();

            /* @phpstan-ignore-next-line */
            return;
        }

        /* @phpstan-ignore-next-line */
        return $this->dispatcher->dispatch($event, $payload, $halt);
    }

    /**
     * Determine if an event should be faked or actually dispatched.
     *
     * @param string $eventName
     * @param mixed $payload
     * @return bool
     */
    protected function shouldFakeEvent($eventName, $payload)
    {
        if (empty($this->eventsToFake)) {
            return true;
        }

        return Collection::make($this->eventsToFake)
            ->filter(function ($event) use ($eventName, $payload) {
                return $event instanceof Closure
                            ? $event($eventName, $payload)
                            : $event === $eventName;
            })
            ->isNotEmpty();
    }
}
