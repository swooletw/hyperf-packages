<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Testing;

use Carbon\Carbon;
use Mockery as m;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\InteractsWithConsole;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\InteractsWithContainer;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\InteractsWithDatabase;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\MakesHttpRequests;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\MocksApplicationServices;
use SwooleTW\Hyperf\Foundation\Testing\DatabaseMigrations;
use SwooleTW\Hyperf\Foundation\Testing\DatabaseTransactions;
use SwooleTW\Hyperf\Foundation\Testing\RefreshDatabase;
use SwooleTW\Hyperf\Foundation\Testing\WithoutEvents;
use SwooleTW\Hyperf\Foundation\Testing\WithoutMiddleware;
use SwooleTW\Hyperf\Support\Facades\Facade;
use Throwable;

class TestCase extends \PHPUnit\Framework\TestCase
{
    use InteractsWithContainer,
        MakesHttpRequests,
        MocksApplicationServices,
        InteractsWithConsole,
        InteractsWithDatabase;

    /**
     * The callbacks that should be run after the application is created.
     *
     * @var array
     */
    protected array $afterApplicationCreatedCallbacks = [];

    /**
     * The callbacks that should be run before the application is destroyed.
     *
     * @var array
     */
    protected array $beforeApplicationDestroyedCallbacks = [];

    /**
     * The exception thrown while running an application destruction callback.
     *
     * @var \Throwable
     */
    protected ?Throwable $callbackException = null;

    /**
     * Indicates if we have made it through the base setUp function.
     *
     * @var bool
     */
    protected bool $setUpHasRun = false;

    protected function setUp(): void
    {
        Facade::clearResolvedInstances();

        /* @phpstan-ignore-next-line */
        if (! $this->app) {
            $this->refreshApplication();
        }

        $this->setUpTraits();

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

        return $uses;
    }

    protected function tearDown(): void
    {
        if ($this->app) {
            $this->callBeforeApplicationDestroyedCallbacks();
            $this->app = null;
        }

        if (class_exists(Carbon::class)) {
            Carbon::setTestNow();
        }

        $this->afterApplicationCreatedCallbacks = [];
        $this->beforeApplicationDestroyedCallbacks = [];

        if ($this->callbackException) {
            throw $this->callbackException;
        }

        try {
            m::close();
        } catch (Throwable) {
        }

        $this->setUpHasRun = false;
    }

    /**
     * Register a callback to be run after the application is created.
     *
     * @param  callable  $callback
     * @return void
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
     *
     * @param  callable  $callback
     * @return void
     */
    protected function beforeApplicationDestroyed(callable $callback)
    {
        $this->beforeApplicationDestroyedCallbacks[] = $callback;
    }

    /**
     * Execute the application's pre-destruction callbacks.
     *
     * @return void
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
}
