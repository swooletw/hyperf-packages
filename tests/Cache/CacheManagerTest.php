<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Cache;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Mockery as m;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Cache\CacheManager;
use SwooleTW\Hyperf\Cache\Contracts\Repository;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CacheManagerTest extends TestCase
{
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
        $repository = m::mock(Repository::class);
        $driver = fn () => $repository;
        $cacheManager->extend('foo', $driver);
        $this->assertEquals($repository, $cacheManager->store('foo'));
    }

    public function testForgetDriver()
    {
        $cacheManager = m::mock(CacheManager::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $cacheManager->shouldReceive('resolve')
            ->withArgs(['array'])
            ->times(4)
            ->andReturn(m::mock(Repository::class));

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

        $count = 0;

        $cacheManager = new CacheManager($app);
        $cacheManager->extend('forget', function () use (&$count) {
            if ($count++ === 0) {
                $repository = m::mock(Repository::class);

                $repository->shouldReceive('forever')->with('foo', 'bar')->once();
                $repository->shouldReceive('get')->with('foo')->once()->andReturn('bar');

                return $repository;
            }

            $repository = m::mock(Repository::class);

            $repository->shouldReceive('get')->with('foo')->once()->andReturnNull();

            return $repository;
        });

        $cacheManager->store('forget')->forever('foo', 'bar');
        $this->assertSame('bar', $cacheManager->store('forget')->get('foo'));
        $cacheManager->forgetDriver('forget');
        $this->assertNull($cacheManager->store('forget')->get('foo'));
    }
}
