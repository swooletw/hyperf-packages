<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Closure;
use Mockery;
use Mockery\Expectation;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use RuntimeException;
use SwooleTW\Hyperf\Foundation\ApplicationContext;

abstract class Facade
{
    /**
     * The resolved object instances.
     */
    protected static array $resolvedInstance;

    /**
     * Run a Closure when the facade has been resolved.
     */
    public static function resolved(Closure $callback): void
    {
        $container = ApplicationContext::getContainer();
        $accessor = static::getFacadeAccessor();

        if ($container->resolved($accessor) === true) {
            $callback(static::getFacadeRoot());
        }

        $container->afterResolving($accessor, function ($service) use ($callback) {
            $callback($service);
        });
    }

    /**
     * Convert the facade into a Mockery spy.
     */
    public static function spy(): ?MockInterface
    {
        if (static::isMock()) {
            return null;
        }

        $class = static::getMockableClass();

        return tap($class ? Mockery::spy($class) : Mockery::spy(), function ($spy) {
            static::swap($spy);
        });
    }

    /**
     * Initiate a partial mock on the facade.
     */
    public static function partialMock(): MockInterface
    {
        $name = static::getFacadeAccessor();

        $mock = static::isMock()
            ? static::$resolvedInstance[$name]
            : static::createFreshMockInstance();

        return $mock->makePartial();
    }

    /**
     * Initiate a mock expectation on the facade.
     */
    public static function shouldReceive(): Expectation
    {
        $name = static::getFacadeAccessor();

        $mock = static::isMock()
                    ? static::$resolvedInstance[$name]
                    : static::createFreshMockInstance();

        return $mock->shouldReceive(...func_get_args());
    }

    /**
     * Create a fresh mock instance for the given class.
     */
    protected static function createFreshMockInstance(): MockInterface
    {
        return tap(static::createMock(), function ($mock) {
            static::swap($mock);

            $mock->shouldAllowMockingProtectedMethods();
        });
    }

    /**
     * Create a fresh mock instance for the given class.
     */
    protected static function createMock(): LegacyMockInterface|MockInterface
    {
        $class = static::getMockableClass();

        return $class ? Mockery::mock($class) : Mockery::mock();
    }

    /**
     * Determines whether a mock is set as the instance of the facade.
     */
    protected static function isMock(): bool
    {
        $name = static::getFacadeAccessor();

        return isset(static::$resolvedInstance[$name])
               && static::$resolvedInstance[$name] instanceof LegacyMockInterface;
    }

    /**
     * Get the mockable class for the bound instance.
     */
    protected static function getMockableClass(): ?string
    {
        if ($root = static::getFacadeRoot()) {
            return get_class($root);
        }

        return null;
    }

    /**
     * Hotswap the underlying instance behind the facade.
     */
    public static function swap(mixed $instance)
    {
        static::$resolvedInstance[static::getFacadeAccessor()] = $instance;

        ApplicationContext::getContainer()->instance(static::getFacadeAccessor(), $instance);
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @return mixed
     * @throws RuntimeException
     */
    public static function __callStatic(string $method, array $args)
    {
        $instance = static::getFacadeRoot();

        if (! $instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance->{$method}(...$args);
    }

    /**
     * Get the root object behind the facade.
     */
    public static function getFacadeRoot(): mixed
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    /**
     * Resolve the facade root instance from the container.
     */
    protected static function resolveFacadeInstance(object|string $name): mixed
    {
        if (is_object($name)) {
            return $name;
        }

        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        if (ApplicationContext::getContainer()->has($name)) {
            return static::$resolvedInstance[$name] = ApplicationContext::getContainer()->get($name);
        }

        return null;
    }

    /**
     * Clear a resolved facade instance.
     */
    public static function clearResolvedInstance(string $name): void
    {
        unset(static::$resolvedInstance[$name]);
    }

    /**
     * Clear all of the resolved instances.
     */
    public static function clearResolvedInstances(): void
    {
        static::$resolvedInstance = [];
    }

    /**
     * Get the registered name of the component.
     *
     * @return object|string
     */
    protected static function getFacadeAccessor()
    {
        throw new RuntimeException('Facade does not implement getFacadeAccessor method.');
    }
}
