<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Broadcasting;

use Exception;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\RequestContext;
use Hyperf\Database\Model\Booted;
use Hyperf\HttpMessage\Server\Request as ServerRequest;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Request;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use SwooleTW\Hyperf\Auth\Contracts\FactoryContract;
use SwooleTW\Hyperf\Broadcasting\Broadcasters\Broadcaster;
use SwooleTW\Hyperf\Database\Eloquent\Model;
use SwooleTW\Hyperf\HttpMessage\Exceptions\HttpException;
use SwooleTW\Hyperf\Support\Facades\Auth;
use SwooleTW\Hyperf\Tests\Foundation\Concerns\HasMockedApplication;

/**
 * @internal
 * @coversNothing
 */
class BroadcasterTest extends TestCase
{
    use HasMockedApplication;

    protected $container;

    public FakeBroadcaster $broadcaster;

    protected function setUp(): void
    {
        parent::setUp();

        $this->broadcaster = new FakeBroadcaster();

        $this->container = $this->getApplication([
            FactoryContract::class => fn () => new stdClass(),
        ]);
        ApplicationContext::setContainer($this->container);
    }

    protected function tearDown(): void
    {
        m::close();
        //
        // Container::setInstance(null);
    }

    public function testExtractingParametersWhileCheckingForUserAccess()
    {
        Booted::$container[BroadcasterTestEloquentModelStub::class] = true;

        $callback = function ($user, BroadcasterTestEloquentModelStub $model, $nonModel) {
        };
        $parameters = $this->broadcaster->extractAuthParameters('asd.{model}.{nonModel}', 'asd.1.something', $callback);
        $this->assertEquals(['model.1.instance', 'something'], $parameters);

        $callback = function ($user, BroadcasterTestEloquentModelStub $model, BroadcasterTestEloquentModelStub $model2, $something) {
        };
        $parameters = $this->broadcaster->extractAuthParameters('asd.{model}.{model2}.{nonModel}', 'asd.1.uid.something', $callback);
        $this->assertEquals(['model.1.instance', 'model.uid.instance', 'something'], $parameters);

        $callback = function ($user) {
        };
        $parameters = $this->broadcaster->extractAuthParameters('asd', 'asd', $callback);
        $this->assertEquals([], $parameters);

        $callback = function ($user, $something) {
        };
        $parameters = $this->broadcaster->extractAuthParameters('asd', 'asd', $callback);
        $this->assertEquals([], $parameters);

        /*
         * Test Explicit Binding...
         */
        // DOTO: 要等 binder 實作
        // $container = new Container;
        // Container::setInstance($container);
        // $binder = m::mock(BindingRegistrar::class);
        // $binder->shouldReceive('getBindingCallback')->times(2)->with('model')->andReturn(function () {
        //     return 'bound';
        // });
        // $container->instance(BindingRegistrar::class, $binder);
        // $callback = function ($user, $model) {
        //     //
        // };
        // $parameters = $this->broadcaster->extractAuthParameters('something.{model}', 'something.1', $callback);
        // $this->assertEquals(['bound'], $parameters);
        // Container::setInstance(new Container);
    }

    public function testCanUseChannelClasses()
    {
        $parameters = $this->broadcaster->extractAuthParameters('asd.{model}.{nonModel}', 'asd.1.something', DummyBroadcastingChannel::class);
        $this->assertEquals(['model.1.instance', 'something'], $parameters);
    }

    // DOTO: 要等 binder 實作
    // public function testModelRouteBinding()
    // {
    //     $container = new Container;
    //     Container::setInstance($container);
    //     $binder = m::mock(BindingRegistrar::class);
    //     $callback = RouteBinding::forModel($container, BroadcasterTestEloquentModelStub::class);
    //
    //     $binder->shouldReceive('getBindingCallback')->times(2)->with('model')->andReturn($callback);
    //     $container->instance(BindingRegistrar::class, $binder);
    //     $callback = function ($user, $model) {
    //         //
    //     };
    //     $parameters = $this->broadcaster->extractAuthParameters('something.{model}', 'something.1', $callback);
    //     $this->assertEquals(['model.1.instance'], $parameters);
    //     Container::setInstance(new Container);
    // }

    public function testUnknownChannelAuthHandlerTypeThrowsException()
    {
        $this->expectException(Exception::class);

        $this->broadcaster->extractAuthParameters('asd.{model}.{nonModel}', 'asd.1.something', 'notClassString');
    }

    public function testCanRegisterChannelsAsClasses()
    {
        $this->broadcaster->channel('something', function () {
        });

        $this->broadcaster->channel('somethingelse', DummyBroadcastingChannel::class);
    }

    public function testNotFoundThrowsHttpException()
    {
        Booted::$container[BroadcasterTestEloquentModelNotFoundStub::class] = true;

        $this->expectException(HttpException::class);

        $callback = function ($user, BroadcasterTestEloquentModelNotFoundStub $model) {
        };
        $this->broadcaster->extractAuthParameters('asd.{model}', 'asd.1', $callback);
    }

    public function testCanRegisterChannelsWithoutOptions()
    {
        $this->broadcaster->channel('somechannel', function () {
        });
    }

    public function testCanRegisterChannelsWithOptions()
    {
        $options = ['a' => ['b', 'c']];
        $this->broadcaster->channel('somechannel', function () {
        }, $options);
    }

    public function testCanRetrieveChannelsOptions()
    {
        $options = ['a' => ['b', 'c']];
        $this->broadcaster->channel('somechannel', function () {
        }, $options);

        $this->assertEquals(
            $options,
            $this->broadcaster->retrieveChannelOptions('somechannel')
        );
    }

    public function testCanRetrieveChannelsOptionsUsingAChannelNameContainingArgs()
    {
        $options = ['a' => ['b', 'c']];
        $this->broadcaster->channel('somechannel.{id}.test.{text}', function () {
        }, $options);

        $this->assertEquals(
            $options,
            $this->broadcaster->retrieveChannelOptions('somechannel.23.test.mytext')
        );
    }

    public function testCanRetrieveChannelsOptionsWhenMultipleChannelsAreRegistered()
    {
        $options = ['a' => ['b', 'c']];
        $this->broadcaster->channel('somechannel', function () {
        });
        $this->broadcaster->channel('someotherchannel', function () {
        }, $options);

        $this->assertEquals(
            $options,
            $this->broadcaster->retrieveChannelOptions('someotherchannel')
        );
    }

    public function testDontRetrieveChannelsOptionsWhenChannelDoesntExists()
    {
        $options = ['a' => ['b', 'c']];
        $this->broadcaster->channel('somechannel', function () {
        }, $options);

        $this->assertEquals(
            [],
            $this->broadcaster->retrieveChannelOptions('someotherchannel')
        );
    }

    public function testRetrieveUserWithoutGuard()
    {
        $this->broadcaster->channel('somechannel', function () {
        });

        Auth::shouldReceive('user')
            ->once()
            ->withNoArgs()
            ->andReturn(new DummyUser());

        $this->assertInstanceOf(
            DummyUser::class,
            $this->broadcaster->retrieveUser('somechannel')
        );
    }

    public function testRetrieveUserWithOneGuardUsingAStringForSpecifyingGuard()
    {
        $this->broadcaster->channel('somechannel', function () {
        }, ['guards' => 'myguard']);

        Auth::shouldReceive('guard')
            ->once()
            ->with('myguard')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->once()
            ->withNoArgs()
            ->andReturn(new DummyUser());

        $this->assertInstanceOf(
            DummyUser::class,
            $this->broadcaster->retrieveUser('somechannel')
        );
    }

    public function testRetrieveUserWithMultipleGuardsAndRespectGuardsOrder()
    {
        $this->broadcaster->channel('somechannel', function () {
        }, ['guards' => ['myguard1', 'myguard2']]);
        $this->broadcaster->channel('someotherchannel', function () {
        }, ['guards' => ['myguard2', 'myguard1']]);

        Auth::shouldReceive('guard')
            ->once()
            ->with('myguard1')
            ->andReturnSelf();
        Auth::shouldReceive('guard')
            ->twice()
            ->with('myguard2')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->times(3)
            ->withNoArgs()
            ->andReturn(null, new DummyUser(), new DummyUser());

        $this->assertInstanceOf(
            DummyUser::class,
            $this->broadcaster->retrieveUser('somechannel')
        );

        $this->assertInstanceOf(
            DummyUser::class,
            $this->broadcaster->retrieveUser('someotherchannel')
        );
    }

    public function testRetrieveUserDontUseDefaultGuardWhenOneGuardSpecified()
    {
        $this->broadcaster->channel('somechannel', function () {
        }, ['guards' => 'myguard']);

        Auth::shouldReceive('guard')
            ->once()
            ->with('myguard')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->once()
            ->withNoArgs()
            ->andReturn(null);
        Auth::shouldNotReceive('guard')
            ->withNoArgs();

        $this->broadcaster->retrieveUser('somechannel');
    }

    public function testRetrieveUserDontUseDefaultGuardWhenMultipleGuardsSpecified()
    {
        $this->broadcaster->channel('somechannel', function () {
        }, ['guards' => ['myguard1', 'myguard2']]);

        Auth::shouldReceive('guard')
            ->once()
            ->with('myguard1')
            ->andReturnSelf();
        Auth::shouldReceive('guard')
            ->once()
            ->with('myguard2')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->twice()
            ->withNoArgs()
            ->andReturn(null);
        Auth::shouldNotReceive('guard')
            ->withNoArgs();

        $this->broadcaster->retrieveUser('somechannel');
    }

    public function testUserAuthenticationWithValidUser()
    {
        $this->broadcaster->resolveAuthenticatedUserUsing(function ($request) {
            return ['id' => '12345', 'socket' => $request->input('socket_id')];
        });

        $this->mockRequest('http://exa.com/foo?socket_id=1234.1234#boom');
        $user = $this->broadcaster->resolveAuthenticatedUser(new Request());

        $this->assertSame([
            'id' => '12345',
            'socket' => '1234.1234',
        ], $user);
    }

    private function mockRequest(?string $uri = null): void
    {
        $request = new ServerRequest('GET', $uri ?: 'http://example.com/foo?bar=baz#boom');
        parse_str($request->getUri()->getQuery(), $result);
        $request = $request->withQueryParams($result);

        RequestContext::set($request);
    }

    public function testUserAuthenticationWithInvalidUser()
    {
        $this->broadcaster->resolveAuthenticatedUserUsing(function ($request) {
            return null;
        });

        $user = $this->broadcaster->resolveAuthenticatedUser(new Request(['socket_id' => '1234.1234']));

        $this->assertNull($user);
    }

    public function testUserAuthenticationWithoutResolve()
    {
        $this->mockRequest('http://exa.com/foo?socket_id=1234.1234#boom');
        $user = $this->broadcaster->resolveAuthenticatedUser(new Request());

        $this->assertNull($this->broadcaster->resolveAuthenticatedUser(new Request()));
    }

    #[DataProvider('channelNameMatchPatternProvider')]
    public function testChannelNameMatchPattern($channel, $pattern, $shouldMatch)
    {
        $this->assertEquals($shouldMatch, $this->broadcaster->channelNameMatchesPattern($channel, $pattern));
    }

    public static function channelNameMatchPatternProvider()
    {
        return [
            ['something', 'something', true],
            ['something.23', 'something.{id}', true],
            ['something.23.test', 'something.{id}.test', true],
            ['something.23.test.42', 'something.{id}.test.{id2}', true],
            ['something-23:test-42', 'something-{id}:test-{id2}', true],
            ['something..test.42', 'something.{id}.test.{id2}', false],
            ['23:string:test', '{id}:string:{text}', true],
            ['something.23', 'something', false],
            ['something.23.test.42', 'something.test.{id}', false],
            ['something-23-test-42', 'something-{id}-test', false],
            ['23:test', '{id}:test:abcd', false],
            ['customer.order.1', 'order.{id}', false],
            ['customerorder.1', 'order.{id}', false],
        ];
    }
}

class FakeBroadcaster extends Broadcaster
{
    public function auth(RequestInterface $request): mixed
    {
        return null;
    }

    public function validAuthenticationResponse(RequestInterface $request, mixed $result): mixed
    {
        return null;
    }

    public function broadcast(array $channels, string $event, array $payload = []): void
    {
    }

    public function extractAuthParameters(string $pattern, string $channel, callable|string $callback): array
    {
        return parent::extractAuthParameters($pattern, $channel, $callback);
    }

    public function retrieveChannelOptions(string $channel): array
    {
        return parent::retrieveChannelOptions($channel);
    }

    public function retrieveUser(string $channel): mixed
    {
        return parent::retrieveUser($channel);
    }

    public function channelNameMatchesPattern(string $channel, string $pattern): bool
    {
        return parent::channelNameMatchesPattern($channel, $pattern);
    }
}

class BroadcasterTestEloquentModelStub extends Model
{
    public function getRouteKeyName()
    {
        return 'id';
    }

    public function where($key, $value)
    {
        $this->value = $value;

        return $this;
    }

    public function firstOrFail()
    {
        return "model.{$this->value}.instance";
    }
}

class BroadcasterTestEloquentModelNotFoundStub extends Model
{
    public function getRouteKeyName()
    {
        return 'id';
    }

    public function where($key, $value)
    {
        $this->value = $value;

        return $this;
    }

    public function firstOrFail()
    {
    }
}

class DummyBroadcastingChannel
{
    public function join($user, BroadcasterTestEloquentModelStub $model, $nonModel)
    {
    }
}

class DummyUser
{
}
