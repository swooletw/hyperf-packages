<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Auth;

use Hyperf\Config\Config;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Mockery as m;
use SwooleTW\Hyperf\Auth\AuthManager;
use SwooleTW\Hyperf\Auth\Contracts\Guard;
use SwooleTW\Hyperf\Auth\Contracts\UserProvider;
use SwooleTW\Hyperf\Auth\Providers\DatabaseUserProvider;
use SwooleTW\Hyperf\Hashing\Contracts\Hasher as HashContract;
use SwooleTW\Hyperf\Tests\TestCase;

use function Hyperf\Coroutine\run;

/**
 * @internal
 * @coversNothing
 */
class AuthMangerTest extends TestCase
{
    public function testGetDefaultDriverFromConfig()
    {
        $manager = new AuthManager($container = $this->getContainer());
        $container->get(ConfigInterface::class)
            ->set('auth.defaults.guard', 'foo');

        $this->assertSame('foo', $manager->getDefaultDriver());
    }

    public function testGetDefaultDriverFromContext()
    {
        $manager = new AuthManager($this->getContainer());

        Context::set('auth.defaults.guard', 'foo');

        run(function () use ($manager) {
            Context::set('auth.defaults.guard', 'bar');

            $this->assertSame('bar', $manager->getDefaultDriver());
        });

        $this->assertSame('foo', $manager->getDefaultDriver());
    }

    public function testExtendDriver()
    {
        $manager = new AuthManager($container = $this->getContainer());
        $container->get(ConfigInterface::class)
            ->set('auth.guards.foo', ['driver' => 'bar']);
        $guard = m::mock(Guard::class);

        $manager->extend('bar', function () use ($guard) {
            return $guard;
        });

        $this->assertSame($guard, $manager->guard('foo'));
    }

    public function testGetDefaultUserProvider()
    {
        $manager = new AuthManager($container = $this->getContainer());
        $container->get(ConfigInterface::class)
            ->set('auth.defaults.provider', 'foo');

        $this->assertSame('foo', $manager->getDefaultUserProvider());
    }

    public function testCreateNullUserProvider()
    {
        $manager = new AuthManager($this->getContainer());

        $this->assertNull($manager->createUserProvider('foo'));
    }

    public function testCreateDatabaseUserProvider()
    {
        $manager = new AuthManager($container = $this->getContainer());

        $container->get(ConfigInterface::class)
            ->set('auth.providers.foo', [
                'driver' => 'database',
                'connection' => 'foo',
                'table' => 'foo',
            ]);

        $resolver = m::mock(ConnectionResolverInterface::class);
        $resolver->shouldReceive('connection')
            ->with('foo')
            ->once()
            ->andReturn(m::mock(ConnectionInterface::class));

        $container->define(ConnectionResolverInterface::class, fn () => $resolver);
        $container->define(HashContract::class, fn () => m::mock(HashContract::class));

        $this->assertInstanceOf(
            DatabaseUserProvider::class,
            $manager->createUserProvider('foo')
        );
    }

    public function testCreateCustomUserProvider()
    {
        $manager = new AuthManager($container = $this->getContainer());

        $container->get(ConfigInterface::class)
            ->set('auth.providers.foo', [
                'driver' => 'bar',
            ]);

        $provider = m::mock(UserProvider::class);
        $manager->provider('bar', fn () => $provider);

        $this->assertSame($provider, $manager->createUserProvider('foo'));
    }

    public function testGetUserResolver()
    {
        $manager = new AuthManager($this->getContainer());

        $manager->resolveUsersUsing(fn () => 'foo');

        run(function () use ($manager) {
            $manager->resolveUsersUsing(fn () => 'bar');

            $this->assertSame('bar', $manager->userResolver()());
        });

        $this->assertSame('foo', $manager->userResolver()());
    }

    protected function getContainer(array $authConfig = [])
    {
        $config = new Config([
            'auth' => $authConfig,
        ]);

        return new Container(
            new DefinitionSource([
                ConfigInterface::class => fn () => $config,
            ])
        );
    }
}
