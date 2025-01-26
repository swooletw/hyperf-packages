<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Testing;

use Hyperf\Coroutine\Coroutine;
use Mockery as m;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\InteractsWithAuthentication;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\InteractsWithConsole;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\InteractsWithContainer;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\InteractsWithDatabase;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\InteractsWithTime;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\MakesHttpRequests;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\MocksApplicationServices;
use SwooleTW\Hyperf\Support\Facades\Facade;
use Throwable;

use function Hyperf\Coroutine\run;

/**
 * @internal
 * @coversNothing
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    use InteractsWithContainer;
    use MakesHttpRequests;
    use InteractsWithAuthentication;
    use InteractsWithConsole;
    use InteractsWithDatabase;
    use InteractsWithTime;
    use MocksApplicationServices;

    /**
     * The callbacks that should be run after the application is created.
     */
    protected array $afterApplicationCreatedCallbacks = [];

    /**
     * The callbacks that should be run before the application is destroyed.
     */
    protected array $beforeApplicationDestroyedCallbacks = [];

    /**
     * The exception thrown while running an application destruction callback.
     */
    protected ?Throwable $callbackException = null;

    /**
     * Indicates if we have made it through the base setUp function.
     */
    protected bool $setUpHasRun = false;

    protected function setUp(): void
    {
        Facade::clearResolvedInstances();

        /* @phpstan-ignore-next-line */
        if (! $this->app) {
            $this->refreshApplication();
        }

        $this->runInCoroutine(
            fn () => $this->setUpTraits()
        );

        foreach ($this->afterApplicationCreatedCallbacks as $callback) {
            $callback();
        }

        $this->setUpHasRun = true;
    }

    /**
     * Boot the testing helper traits.
     *
     * @return array
     */
    protected function setUpTraits()
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[RefreshDatabase::class])) {
            $this->refreshDatabase();
        }

        if (isset($uses[DatabaseMigrations::class])) {
            $this->runDatabaseMigrations();
        }

        if (isset($uses[DatabaseTransactions::class])) {
            $this->beginDatabaseTransaction();
        }

        if (isset($uses[WithoutMiddleware::class])) {
            $this->disableMiddlewareForAllTests();
        }

        if (isset($uses[WithoutEvents::class])) {
            $this->disableEventsForAllTests();
        }

        foreach ($uses as $trait) {
            if (method_exists($this, $method = 'setUp' . class_basename($trait))) {
                $this->{$method}();
            }

            if (method_exists($this, $method = 'tearDown' . class_basename($trait))) {
                $this->beforeApplicationDestroyed(fn () => $this->{$method}());
            }
        }

        return $uses;
    }

    protected function tearDown(): void
    {
        if ($this->app) {
            $this->runInCoroutine(
                fn () => $this->callBeforeApplicationDestroyedCallbacks()
            );
            $this->flushApplication();
        }

        $this->afterApplicationCreatedCallbacks = [];
        $this->beforeApplicationDestroyedCallbacks = [];

        if ($this->callbackException) {
            throw $this->callbackException;
        }

        try {
            if ($container = m::getContainer()) {
                $this->addToAssertionCount($container->mockery_getExpectationCount());
            }
            m::close();
        } catch (Throwable) {
        }

        $this->setUpHasRun = false;
    }

    /**
     * Register a callback to be run after the application is created.
     */
    public function afterApplicationCreated(callable $callback)
    {
        $this->afterApplicationCreatedCallbacks[] = $callback;

        if ($this->setUpHasRun) {
            $callback();
        }
    }

    /**
     * Register a callback to be run before the application is destroyed.
     */
    protected function beforeApplicationDestroyed(callable $callback)
    {
        $this->beforeApplicationDestroyedCallbacks[] = $callback;
    }

    /**
     * Execute the application's pre-destruction callbacks.
     */
    protected function callBeforeApplicationDestroyedCallbacks()
    {
        foreach ($this->beforeApplicationDestroyedCallbacks as $callback) {
            try {
                $callback();
            } catch (Throwable $e) {
                if (! $this->callbackException) {
                    $this->callbackException = $e;
                }
            }
        }
    }

    /**
     * Ensure callback is executed in coroutine.
     */
    protected function runInCoroutine(callable $callback): void
    {
        Coroutine::inCoroutine() ? $callback() : run($callback);
    }
}
