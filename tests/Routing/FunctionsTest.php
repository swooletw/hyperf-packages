<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Routing;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use Mockery;
use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Routing\UrlGenerator;

use function SwooleTW\Hyperf\Routing\route;

/**
 * @internal
 * @coversNothing
 */
class FunctionsTest extends TestCase
{
    public function testRoute()
    {
        ! defined('BASE_PATH') && define('BASE_PATH', __DIR__);

        $container = Mockery::mock(ContainerInterface::class);
        $urlGenerator = Mockery::mock(UrlGenerator::class);

        $container->shouldReceive('get')
            ->with(UrlGenerator::class)
            ->andReturn($urlGenerator);

        $urlGenerator->shouldReceive('route')
            ->with('foo', ['bar'], 'http')
            ->andReturn('foo-bar');

        $urlGenerator->shouldReceive('route')
            ->with('foo', ['bar'], 'baz')
            ->andReturn('foo-bar-baz');

        ApplicationContext::setContainer($container);

        $this->assertEquals('foo-bar', route('foo', ['bar']));
        $this->assertEquals('foo-bar-baz', route('foo', ['bar'], 'baz'));
    }
}
