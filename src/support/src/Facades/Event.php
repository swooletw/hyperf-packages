<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Closure;
use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Register;
use Hyperf\Event\ListenerData;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Event\EventDispatcher;
use SwooleTW\Hyperf\Event\QueuedClosure;
use SwooleTW\Hyperf\Support\Testing\Fakes\EventFake;

/**
 * @method static object|string dispatch(object|string $event, mixed $payload = [], bool $halt = false)
 * @method static void listen(array|Closure|QueuedClosure|string $events, null|array|Closure|QueuedClosure|string|int $listener = null, int $priority = ListenerData::DEFAULT_PRIORITY)
 * @method static object|string until(object|string $event, mixed $payload = [])
 * @method static iterable getListeners(object|string $eventName)
 * @method static void push(string $event, mixed $payload = [])
 * @method static void flush(string $event)
 * @method static void forgetPushed()
 * @method static void forget(object|string $event)
 * @method static bool hasListeners(object|string $eventName)
 * @method static bool hasWildcardListeners(string $eventName)
 * @method static EventDispatcher setQueueResolver(callable $resolver)
 * @method static void subscribe(object|string $subscriber)
 * @method static EventFake except(array|string $eventsToDispatch)
 * @method static void assertListening(string $expectedEvent, string|array $expectedListener)
 * @method static void assertDispatched(string|\Closure $event, callable|int|null $callback = null)
 * @method static void assertDispatchedTimes(string $event, int $times = 1)
 * @method static void assertNotDispatched(string|\Closure $event, callable|null $callback = null)
 * @method static void assertNothingDispatched()
 * @method static Collection dispatched(string $event, callable|null $callback = null)
 * @method static bool hasDispatched(string $event)
 *
 * @see EventDispatcher
 * @see EventFake
 */
class Event extends Facade
{
    /**
     * Replace the bound instance with a fake.
     */
    public static function fake(array|string $eventsToFake = []): EventFake
    {
        static::swap($fake = new EventFake(static::getFacadeRoot(), $eventsToFake));

        Register::setEventDispatcher($fake);

        return $fake;
    }

    /**
     * Replace the bound instance with a fake that fakes all events except the given events.
     */
    public static function fakeExcept(array|string $eventsToAllow): EventFake
    {
        return static::fake([
            function ($eventName) use ($eventsToAllow) {
                return ! in_array($eventName, (array) $eventsToAllow);
            },
        ]);
    }

    /**
     * Replace the bound instance with a fake during the given callable's execution.
     */
    public static function fakeFor(callable $callable, array $eventsToFake = []): mixed
    {
        $originalDispatcher = static::getFacadeRoot();

        static::fake($eventsToFake);

        return tap($callable(), function () use ($originalDispatcher) {
            static::swap($originalDispatcher);

            Register::setEventDispatcher($originalDispatcher);
        });
    }

    /**
     * Replace the bound instance with a fake during the given callable's execution.
     */
    public static function fakeExceptFor(callable $callable, array $eventsToAllow = []): mixed
    {
        $originalDispatcher = static::getFacadeRoot();

        static::fakeExcept($eventsToAllow);

        return tap($callable(), function () use ($originalDispatcher) {
            static::swap($originalDispatcher);

            Register::setEventDispatcher($originalDispatcher);
        });
    }

    protected static function getFacadeAccessor()
    {
        return EventDispatcherInterface::class;
    }
}
