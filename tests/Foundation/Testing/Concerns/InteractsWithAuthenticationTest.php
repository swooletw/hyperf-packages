<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Foundation\Testing\Concerns;

use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Mockery;
use SwooleTW\Hyperf\Auth\Contracts\Authenticatable as UserContract;
use SwooleTW\Hyperf\Auth\Contracts\FactoryContract as AuthManagerContract;
use SwooleTW\Hyperf\Auth\Contracts\Guard;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\InteractsWithAuthentication;
use SwooleTW\Hyperf\Tests\Foundation\Testing\ApplicationTestCase;

/**
 * @internal
 * @coversNothing
 */
class InteractsWithAuthenticationTest extends ApplicationTestCase
{
    use InteractsWithAuthentication;

    public function tearDown(): void
    {
        parent::tearDown();

        Context::destroy('__auth.defaults.guard');
    }

    public function testAssertAsGuest()
    {
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('check')
            ->once()
            ->andReturn(false);

        $this->app->get(AuthManagerContract::class)
            ->extend('foo', fn () => $guard);
        $this->app->get(ConfigInterface::class)
            ->set('auth.guards.foo', [
                'driver' => 'foo',
                'provider' => 'users',
            ]);

        Context::set('__auth.defaults.guard', 'foo');

        $this->assertGuest();
        $this->assertFalse($this->isAuthenticated());
    }

    public function testAssertActingAs()
    {
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('check')
            ->once()
            ->andReturn(true);
        $guard->shouldReceive('setUser')
            ->once()
            ->andReturn($user = Mockery::mock(UserContract::class));
        $guard->shouldReceive('user')
            ->once()
            ->andReturn($user);
        $user->shouldReceive('getAuthIdentifier')
            ->twice()
            ->andReturn('id');

        $this->app->get(AuthManagerContract::class)
            ->extend('foo', fn () => $guard);
        $this->app->get(ConfigInterface::class)
            ->set('auth.guards.foo', [
                'driver' => 'foo',
                'provider' => 'users',
            ]);

        Context::set('__auth.defaults.guard', 'foo');

        $this->actingAs($user);

        $this->assertTrue($this->isAuthenticated());
        $this->assertAuthenticatedAs($user);
    }
}
