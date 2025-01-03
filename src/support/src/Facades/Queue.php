<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Foundation\ApplicationContext;
use SwooleTW\Hyperf\Queue\Contracts\Factory as FactoryContract;
use SwooleTW\Hyperf\Queue\Worker;
use SwooleTW\Hyperf\Support\Testing\Fakes\QueueFake;

use function Hyperf\Tappable\tap;

/**
 * @method static void before(mixed $callback)
 * @method static void after(mixed $callback)
 * @method static void exceptionOccurred(mixed $callback)
 * @method static void looping(mixed $callback)
 * @method static void failing(mixed $callback)
 * @method static void stopping(mixed $callback)
 * @method static bool connected(string|null $name = null)
 * @method static \SwooleTW\Hyperf\Queue\Contracts\Queue connection(string|null $name = null)
 * @method static void extend(string $driver, \Closure $resolver)
 * @method static void addConnector(string $driver, \Closure $resolver)
 * @method static string getDefaultDriver()
 * @method static void setDefaultDriver(string $name)
 * @method static string getName(string|null $connection = null)
 * @method static \Psr\Container\ContainerInterface getContainer()
 * @method static \SwooleTW\Hyperf\Queue\QueueManager setContainer(\Psr\Container\ContainerInterface $app)
 * @method static int size(string|null $queue = null)
 * @method static mixed push(string|object $job, mixed $data = '', string|null $queue = null)
 * @method static mixed pushOn(string $queue, string|object $job, mixed $data = '')
 * @method static mixed pushRaw(string $payload, string|null $queue = null, array $options = [])
 * @method static mixed later(\DateTimeInterface|\DateInterval|int $delay, string|object $job, mixed $data = '', string|null $queue = null)
 * @method static mixed laterOn(string $queue, \DateTimeInterface|\DateInterval|int $delay, string|object $job, mixed $data = '')
 * @method static mixed bulk(array $jobs, mixed $data = '', string|null $queue = null)
 * @method static \SwooleTW\Hyperf\Queue\Contracts\Job|null pop(string|null $queue = null)
 * @method static string getConnectionName()
 * @method static \SwooleTW\Hyperf\Queue\Contracts\Queue setConnectionName(string $name)
 * @method static mixed getJobTries(mixed $job)
 * @method static mixed getJobBackoff(mixed $job)
 * @method static mixed getJobExpiration(mixed $job)
 * @method static void createPayloadUsing(callable|null $callback)
 * @method static \Illuminate\Container\Container getContainer()
 * @method static void setContainer(\Illuminate\Container\Container $container)
 * @method static \SwooleTW\Hyperf\Support\Testing\Fakes\QueueFake except(array|string $jobsToBeQueued)
 * @method static void assertPushed(string|\Closure $job, callable|int|null $callback = null)
 * @method static void assertPushedOn(string $queue, string|\Closure $job, callable|null $callback = null)
 * @method static void assertPushedWithChain(string $job, array $expectedChain = [], callable|null $callback = null)
 * @method static void assertPushedWithoutChain(string $job, callable|null $callback = null)
 * @method static void assertClosurePushed(callable|int|null $callback = null)
 * @method static void assertClosureNotPushed(callable|null $callback = null)
 * @method static void assertNotPushed(string|\Closure $job, callable|null $callback = null)
 * @method static void assertCount(int $expectedCount)
 * @method static void assertNothingPushed()
 * @method static \Hyperf\Collection\Collection pushed(string $job, callable|null $callback = null)
 * @method static bool hasPushed(string $job)
 * @method static bool shouldFakeJob(object $job)
 * @method static array pushedJobs()
 * @method static \SwooleTW\Hyperf\Support\Testing\Fakes\QueueFake serializeAndRestore(bool $serializeAndRestore = true)
 *
 * @see \SwooleTW\Hyperf\Queue\QueueManager
 * @see \SwooleTW\Hyperf\Queue\Queue
 * @see \SwooleTW\Hyperf\Support\Testing\Fakes\QueueFake
 */
class Queue extends Facade
{
    /**
     * Register a callback to be executed to pick jobs.
     */
    public static function popUsing(string $workerName, callable $callback): void
    {
        Worker::popUsing($workerName, $callback);
    }

    /**
     * Replace the bound instance with a fake.
     */
    public static function fake(array|string $jobsToFake = []): QueueFake
    {
        $actualQueueManager = static::isFake()
            ? static::getFacadeRoot()->queue
            : static::getFacadeRoot();

        return tap(new QueueFake(
            ApplicationContext::getContainer(),
            $jobsToFake,
            $actualQueueManager
        ), function ($fake) {
            static::swap($fake);
        });
    }

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return FactoryContract::class;
    }
}
