<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Broadcasting;

use Ably\AblyRest;
use Hyperf\HttpServer\Request;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use stdClass;
use SwooleTW\Hyperf\Auth\Contracts\FactoryContract;
use SwooleTW\Hyperf\Broadcasting\Broadcasters\AblyBroadcaster;
use SwooleTW\Hyperf\Foundation\ApplicationContext;
use SwooleTW\Hyperf\HttpMessage\Exceptions\AccessDeniedHttpException;
use SwooleTW\Hyperf\Support\Facades\Auth;
use SwooleTW\Hyperf\Support\Facades\Facade;
use SwooleTW\Hyperf\Tests\Foundation\Concerns\HasMockedApplication;

class AblyBroadcasterTest extends TestCase
{
    use HasMockedApplication;

    public AblyBroadcaster $broadcaster;
    public AblyRest $ably;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ably = m::mock(AblyRest::class, ['abcd:efgh']);

        $this->broadcaster = m::mock(AblyBroadcaster::class, [$this->ably])->makePartial();

        $container = $this->getApplication([
            FactoryContract::class => fn () => new stdClass(),
        ]);
        ApplicationContext::setContainer($container);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();

        Facade::clearResolvedInstances();
    }

    public function testAuthCallValidAuthenticationResponseWithPrivateChannelWhenCallbackReturnTrue()
    {
        $this->broadcaster->channel('test', function () {
            return true;
        });

        $this->broadcaster->shouldReceive('validAuthenticationResponse')->once();

        $this->broadcaster->auth(
            $this->getMockRequestWithUserForChannel('private-test')
        );
    }

    public function testAuthThrowAccessDeniedHttpExceptionWithPrivateChannelWhenCallbackReturnFalse()
    {
        $this->expectException(AccessDeniedHttpException::class);

        $this->broadcaster->channel('test', function () {
            return false;
        });

        $this->broadcaster->auth(
            $this->getMockRequestWithUserForChannel('private-test')
        );
    }

    public function testAuthThrowAccessDeniedHttpExceptionWithPrivateChannelWhenRequestUserNotFound()
    {
        $this->expectException(AccessDeniedHttpException::class);

        $this->broadcaster->channel('test', function () {
            return true;
        });

        $this->broadcaster->auth(
            $this->getMockRequestWithoutUserForChannel('private-test')
        );
    }

    public function testAuthCallValidAuthenticationResponseWithPresenceChannelWhenCallbackReturnAnArray()
    {
        $returnData = [1, 2, 3, 4];
        $this->broadcaster->channel('test', function () use ($returnData) {
            return $returnData;
        });

        $this->broadcaster->shouldReceive('validAuthenticationResponse')->once();

        $this->broadcaster->auth(
            $this->getMockRequestWithUserForChannel('presence-test')
        );
    }

    public function testAuthThrowAccessDeniedHttpExceptionWithPresenceChannelWhenCallbackReturnNull()
    {
        $this->expectException(AccessDeniedHttpException::class);

        $this->broadcaster->channel('test', function () {
        });

        $this->broadcaster->auth(
            $this->getMockRequestWithUserForChannel('presence-test')
        );
    }

    public function testAuthThrowAccessDeniedHttpExceptionWithPresenceChannelWhenRequestUserNotFound()
    {
        $this->expectException(AccessDeniedHttpException::class);

        $this->broadcaster->channel('test', function () {
            return [1, 2, 3, 4];
        });

        $this->broadcaster->auth(
            $this->getMockRequestWithoutUserForChannel('presence-test')
        );
    }

    protected function getMockRequestWithUserForChannel(string $channel): Request
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('input')->with('channel_name')->andReturn($channel);

        $user = m::mock('User');
        $user->shouldReceive('getAuthIdentifierForBroadcasting')
             ->andReturn(42);
        $user->shouldReceive('getAuthIdentifier')
             ->andReturn(42);

        Auth::shouldReceive('user')->andReturn($user);

        return $request;
    }

    protected function getMockRequestWithoutUserForChannel(string $channel): Request
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('input')->with('channel_name')->andReturn($channel);

        Auth::shouldReceive('user')->andReturn(null);

        return $request;
    }
}
