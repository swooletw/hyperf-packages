<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Hyperf\Coroutine\Coroutine;
use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

use function Hyperf\Coroutine\run;

/**
 * @internal
 * @coversNothing
 */
class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        if ($container = Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        Mockery::close();

        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
    }

    protected function runInCoroutine(callable $callback): void
    {
        Coroutine::inCoroutine()
            ? Coroutine::create($callback)
            : run($callback);
    }
}
