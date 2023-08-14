<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Cache;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Cache\ArrayStore;
use SwooleTW\Hyperf\Cache\CacheManager;

/**
 * @internal
 * @coversNothing
 */
class CacheManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testCustomDriverClosureBoundObjectIsCacheManager()
    {
        $app = m::mock(ContainerInterface::class);
        $app->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'laravel_cache' => [
                'stores' => [
                    'foo' => [
                        'driver' => 'foo',
                    ],
                ],
            ],
        ]));
        $cacheManager = new CacheManager($app);
        $driver = function () {
            return $this;
        };
        $cacheManager->extend('foo', $driver);
        $this->assertEquals($cacheManager, $cacheManager->store('foo'));
    }

    public function testForgetDriver()
    {
        $cacheManager = m::mock(CacheManager::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $cacheManager->shouldReceive('resolve')
            ->withArgs(['array'])
            ->times(4)
            ->andReturn(new ArrayStore());

        $cacheManager->shouldReceive('getDefaultDriver')
            ->once()
            ->andReturn('array');

        foreach (['array', ['array'], null] as $option) {
            $cacheManager->store('array');
            $cacheManager->store('array');
            $cacheManager->forgetDriver($option);
            $cacheManager->store('array');
            $cacheManager->store('array');
        }

        $this->assertTrue(true);
    }

    public function testForgetDriverForgets()
    {
        $app = m::mock(ContainerInterface::class);
        $app->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'laravel_cache' => [
                'stores' => [
                    'forget' => [
                        'driver' => 'forget',
                    ],
                ],
            ],
        ]));
        $cacheManager = new CacheManager($app);
        $cacheManager->extend('forget', function () {
            return new ArrayStore();
        });

        $cacheManager->store('forget')->forever('foo', 'bar');
        $this->assertSame('bar', $cacheManager->store('forget')->get('foo'));
        $cacheManager->forgetDriver('forget');
        $this->assertNull($cacheManager->store('forget')->get('foo'));
    }
}
