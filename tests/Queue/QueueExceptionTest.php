<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Queue;

use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Queue\Exceptions\MaxAttemptsExceededException;
use SwooleTW\Hyperf\Queue\Exceptions\TimeoutExceededException;
use SwooleTW\Hyperf\Queue\Jobs\RedisJob;

/**
 * @internal
 * @coversNothing
 */
class QueueExceptionTest extends TestCase
{
    public function testCreateTimeoutExceptionForJob()
    {
        $e = TimeoutExceededException::forJob($job = new MyFakeRedisJob());

        $this->assertSame('App\Jobs\UnderlyingJob has timed out.', $e->getMessage());
        $this->assertSame($job, $e->job);
    }

    public function testCreateMaxAttemptsExceptionForJob()
    {
        $e = MaxAttemptsExceededException::forJob($job = new MyFakeRedisJob());

        $this->assertSame('App\Jobs\UnderlyingJob has been attempted too many times.', $e->getMessage());
        $this->assertSame($job, $e->job);
    }
}

class MyFakeRedisJob extends RedisJob
{
    public function __construct()
    {
    }

    public function resolveName(): string
    {
        return 'App\Jobs\UnderlyingJob';
    }
}
