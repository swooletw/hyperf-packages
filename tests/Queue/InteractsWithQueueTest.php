<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Queue;

use Exception;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Queue\Contracts\Job;
use SwooleTW\Hyperf\Queue\InteractsWithQueue;

/**
 * @internal
 * @coversNothing
 */
class InteractsWithQueueTest extends TestCase
{
    public function testCreatesAnExceptionFromString()
    {
        $queueJob = m::mock(Job::class);
        $queueJob->shouldReceive('fail')->withArgs(function ($e) {
            $this->assertInstanceOf(Exception::class, $e);
            $this->assertEquals('Whoops!', $e->getMessage());

            return true;
        });

        $job = new class {
            use InteractsWithQueue;

            public ?Job $job;
        };

        $job->job = $queueJob;
        $job->fail('Whoops!');
    }
}
