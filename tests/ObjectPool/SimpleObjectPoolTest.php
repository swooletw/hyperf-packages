<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\ObjectPool;

use Hyperf\Context\ApplicationContext;
use Mockery;
use Psr\Container\ContainerInterface;
use stdClass;
use SwooleTW\Hyperf\ObjectPool\SimpleObjectPool;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class SimpleObjectPoolTest extends TestCase
{
    public function testCreateObject()
    {
        $container = $this->getContainer();
        $object = new stdClass();
        $pool = new SimpleObjectPool($container, fn () => $object);

        $this->assertSame($object, $pool->get());
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        return $container;
    }
}
