<?php

declare(strict_types=1);

namespace Illuminate\Tests\Broadcasting;

use Hyperf\HttpServer\Request;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Pusher\Pusher;
use stdClass;
use SwooleTW\Hyperf\Auth\Contracts\FactoryContract;
use SwooleTW\Hyperf\Broadcasting\Broadcasters\PusherBroadcaster;
use SwooleTW\Hyperf\Foundation\ApplicationContext;
use SwooleTW\Hyperf\HttpMessage\Exceptions\AccessDeniedHttpException;
use SwooleTW\Hyperf\Support\Facades\Auth;
use SwooleTW\Hyperf\Support\Facades\Facade;
use SwooleTW\Hyperf\Tests\Foundation\Concerns\HasMockedApplication;

/**
 * @internal
 * @coversNothing
 */
class PusherBroadcasterTest extends TestCase
{
    use HasMockedApplication;

    public PusherBroadcaster $broadcaster;

    public Pusher $pusher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pusher = m::mock(Pusher::class);
        $this->broadcaster = m::mock(PusherBroadcaster::class, [$this->pusher])->makePartial();

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

    public function testValidAuthenticationResponseCallPusherSocketAuthMethodWithPrivateChannel()
    {
        $request = $this->getMockRequestWithUserForChannel('private-test');

        $data = [
            'auth' => 'abcd:efgh',
        ];

        $this->pusher->shouldReceive('authorizeChannel')
            ->once()
            ->andReturn(json_encode($data));

        $this->assertEquals(
            $data,
            $this->broadcaster->validAuthenticationResponse($request, true)
        );
    }

    public function testValidAuthenticationResponseCallPusherPresenceAuthMethodWithPresenceChannel()
    {
        $request = $this->getMockRequestWithUserForChannel('presence-test');

        $data = [
            'auth' => 'abcd:efgh',
            'channel_data' => [
                'user_id' => 42,
                'user_info' => [1, 2, 3, 4],
            ],
        ];

        $this->pusher->shouldReceive('authorizePresenceChannel')
            ->once()
            ->andReturn(json_encode($data));

        $this->assertEquals(
            $data,
            $this->broadcaster->validAuthenticationResponse($request, true)
        );
    }

    public function testUserAuthenticationForPusher()
    {
        $authenticateUser = [
            'auth' => '278d425bdf160c739803:4708d583dada6a56435fb8bc611c77c359a31eebde13337c16ab43aa6de336ba',
            'user_data' => json_encode(['id' => '12345']),
        ];

        $this->pusher
            ->shouldReceive('authenticateUser')
            ->andReturn(json_encode($authenticateUser));

        $this->broadcaster = new PusherBroadcaster($this->pusher);

        $this->broadcaster->resolveAuthenticatedUserUsing(function () {
            return ['id' => '12345'];
        });

        $response = $this->broadcaster->resolveAuthenticatedUser(
            $this->getMockRequestWithUserForChannel('presence-test')
        );

        $this->assertSame($authenticateUser, $response);
    }

    protected function getMockRequestWithUserForChannel(string $channel): Request
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('input')->with('channel_name')->andReturn($channel);
        $request->shouldReceive('input')->with('socket_id')->andReturn('1234.1234');

        $user = m::mock('User');
        $user->shouldReceive('getAuthIdentifier')->andReturn(42);

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
