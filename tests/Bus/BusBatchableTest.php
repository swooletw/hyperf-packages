<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Bus;

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Bus\Batch;
use SwooleTW\Hyperf\Bus\Batchable;
use SwooleTW\Hyperf\Bus\Contracts\BatchRepository;

/**
 * @internal
 * @coversNothing
 */
class BusBatchableTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testBatchMayBeRetrieved()
    {
        $class = new class {
            use Batchable;
        };

        $this->assertSame($class, $class->withBatchId('test-batch-id'));
        $this->assertSame('test-batch-id', $class->batchId);

        $batch = m::mock(Batch::class);
        $repository = m::mock(BatchRepository::class);
        $repository->shouldReceive('find')->once()->with('test-batch-id')->andReturn($batch);

        $container = new Container(
            new DefinitionSource([
                BatchRepository::class => fn () => $repository,
            ])
        );
        ApplicationContext::setContainer($container);

        $this->assertSame($batch, $class->batch());
    }
}
