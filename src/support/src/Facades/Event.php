<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\Database\Model\Register;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Support\Testing\Fakes\EventFake;

/**
 * @mixin EventDispatcher
 */
class Event extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @param array|string $eventsToFake
     * @return \SwooleTW\Hyperf\Support\Testing\Fakes\EventFake
     */
    public static function fake($eventsToFake = [])
    {
        static::swap($fake = new EventFake(static::getFacadeRoot(), $eventsToFake));

        Register::setEventDispatcher($fake);

        return $fake;
    }

    /**
     * Replace the bound instance with a fake that fakes all events except the given events.
     *
     * @param string|string[] $eventsToAllow
     * @return \SwooleTW\Hyperf\Support\Testing\Fakes\EventFake
     */
    public static function fakeExcept($eventsToAllow)
    {
        return static::fake([
            function ($eventName) use ($eventsToAllow) {
                return ! in_array($eventName, (array) $eventsToAllow);
            },
        ]);
    }

    /**
     * Replace the bound instance with a fake during the given callable's execution.
     *
     * @return mixed
     */
    public static function fakeFor(callable $callable, array $eventsToFake = [])
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
     *
     * @return mixed
     */
    public static function fakeExceptFor(callable $callable, array $eventsToAllow = [])
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
