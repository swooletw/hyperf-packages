<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Bus\Contracts\BatchRepository;
use SwooleTW\Hyperf\Bus\Contracts\Dispatcher as BusDispatcherContract;
use SwooleTW\Hyperf\Bus\PendingChain;
use SwooleTW\Hyperf\Bus\PendingDispatch;
use SwooleTW\Hyperf\Support\Testing\Fakes\BusFake;

use function Hyperf\Tappable\tap;

/**
 * @method static mixed dispatch(mixed $command)
 * @method static mixed dispatchSync(mixed $command, mixed $handler = null)
 * @method static mixed dispatchNow(mixed $command, mixed $handler = null)
 * @method static \SwooleTW\Hyperf\Bus\Batch|null findBatch(string $batchId)
 * @method static \SwooleTW\Hyperf\Bus\PendingBatch batch(\Hyperf\Collection\Collection|array|mixed $jobs)
 * @method static \SwooleTW\Hyperf\Bus\PendingChain chain(\Hyperf\Collection\Collection|array $jobs)
 * @method static bool hasCommandHandler(mixed $command)
 * @method static bool|mixed getCommandHandler(mixed $command)
 * @method static mixed dispatchToQueue(mixed $command)
 * @method static void dispatchAfterResponse(mixed $command, mixed $handler = null)
 * @method static \SwooleTW\Hyperf\Bus\Dispatcher pipeThrough(array $pipes)
 * @method static \SwooleTW\Hyperf\Bus\Dispatcher map(array $map)
 * @method static \SwooleTW\Hyperf\Support\Testing\Fakes\BusFake except(array|string $jobsToDispatch)
 * @method static void assertDispatched(string|\Closure $command, callable|int|null $callback = null)
 * @method static void assertDispatchedTimes(string|\Closure $command, int $times = 1)
 * @method static void assertNotDispatched(string|\Closure $command, callable|null $callback = null)
 * @method static void assertNothingDispatched()
 * @method static void assertDispatchedSync(string|\Closure $command, callable|int|null $callback = null)
 * @method static void assertDispatchedSyncTimes(string|\Closure $command, int $times = 1)
 * @method static void assertNotDispatchedSync(string|\Closure $command, callable|null $callback = null)
 * @method static void assertDispatchedAfterResponse(string|\Closure $command, callable|int|null $callback = null)
 * @method static void assertDispatchedAfterResponseTimes(string|\Closure $command, int $times = 1)
 * @method static void assertNotDispatchedAfterResponse(string|\Closure $command, callable|null $callback = null)
 * @method static void assertChained(array $expectedChain)
 * @method static void assertNothingChained()
 * @method static void assertDispatchedWithoutChain(string|\Closure $command, callable|null $callback = null)
 * @method static \SwooleTW\Hyperf\Support\Testing\Fakes\ChainedBatchTruthTest chainedBatch(\Closure $callback)
 * @method static void assertBatched(callable $callback)
 * @method static void assertBatchCount(int $count)
 * @method static void assertNothingBatched()
 * @method static void assertNothingPlaced()
 * @method static \Hyperf\Collection\Collection dispatched(string $command, callable|null $callback = null)
 * @method static \Hyperf\Collection\Collection dispatchedSync(string $command, callable|null $callback = null)
 * @method static \Hyperf\Collection\Collection dispatchedAfterResponse(string $command, callable|null $callback = null)
 * @method static \Hyperf\Collection\Collection batched(callable $callback)
 * @method static bool hasDispatched(string $command)
 * @method static bool hasDispatchedSync(string $command)
 * @method static bool hasDispatchedAfterResponse(string $command)
 * @method static \SwooleTW\Hyperf\Bus\Batch dispatchFakeBatch(string $name = '')
 * @method static \SwooleTW\Hyperf\Bus\Batch recordPendingBatch(\Illuminate\Bus\PendingBatch $pendingBatch)
 * @method static \SwooleTW\Hyperf\Support\Testing\Fakes\BusFake serializeAndRestore(bool $serializeAndRestore = true)
 * @method static array dispatchedBatches()
 *
 * @see \SwooleTW\Hyperf\Bus\Dispatcher
 * @see \SwooleTW\Hyperf\Support\Testing\Fakes\BusFake
 */
class Bus extends Facade
{
    /**
     * Replace the bound instance with a fake.
     */
    public static function fake(array|string $jobsToFake = [], ?BatchRepository $batchRepository = null): BusFake
    {
        $actualDispatcher = static::isFake()
            ? static::getFacadeRoot()->dispatcher
            : static::getFacadeRoot();

        return tap(new BusFake($actualDispatcher, $jobsToFake, $batchRepository), function ($fake) {
            static::swap($fake);
        });
    }

    /**
     * Dispatch the given chain of jobs.
     *
     * @param array|mixed $jobs
     */
    public static function dispatchChain(mixed $jobs): PendingDispatch
    {
        $jobs = is_array($jobs) ? $jobs : func_get_args();

        return (new PendingChain(array_shift($jobs), $jobs))
            ->dispatch();
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BusDispatcherContract::class;
    }
}
