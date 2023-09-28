<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Auth\Access;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Database\Model\Model;
use Mockery;
use Mockery\MockInterface;
use SwooleTW\Hyperf\Auth\Access\Response;
use SwooleTW\Hyperf\Auth\Contracts\Gate;
use SwooleTW\Hyperf\Tests\Auth\Stub\AuthorizesRequestsStub;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AuthorizesRequestsTest extends TestCase
{
    public function testAuthorize()
    {
        $response = Mockery::mock(Response::class);

        $gate = $this->mockGate();

        $gate->shouldReceive('authorize')->with('foo', ['bar'])->once()->andReturn($response);

        $this->assertEquals($response, (new AuthorizesRequestsStub())->authorize('foo', ['bar']));
    }

    public function testAuthorizeMayBeGuessedPassingModelInstance()
    {
        $model = new class() extends Model {};
        $response = Mockery::mock(Response::class);

        $gate = $this->mockGate();

        $gate->shouldReceive('authorize')->with(__FUNCTION__, $model)->once()->andReturn($response);

        $this->assertEquals($response, (new AuthorizesRequestsStub())->authorize($model));
    }

    public function testAuthorizeMayBeGuessedPassingClassName()
    {
        $class = Model::class;
        $response = Mockery::mock(Response::class);

        $gate = $this->mockGate();

        $gate->shouldReceive('authorize')->with(__FUNCTION__, $class)->once()->andReturn($response);

        $this->assertEquals($response, (new AuthorizesRequestsStub())->authorize($class));
    }

    public function testAuthorizeMayBeGuessedAndNormalized()
    {
        $model = new class() extends Model {};
        $response = Mockery::mock(Response::class);

        $gate = $this->mockGate();

        $gate->shouldReceive('authorize')->with('create', $model)->once()->andReturn($response);

        $this->assertEquals($response, (new class() extends AuthorizesRequestsStub {
            public function store($model)
            {
                return $this->authorize($model);
            }
        })->store($model));
    }

    /**
     * @return Gate|MockInterface
     */
    private function mockGate(): Gate
    {
        ! defined('BASE_PATH') && define('BASE_PATH', __DIR__);

        $gate = Mockery::mock(Gate::class);

        /** @var ContainerInterface|MockInterface */
        $container = Mockery::mock(ContainerInterface::class);

        $container->shouldReceive('get')->with(Gate::class)->andReturn($gate);

        ApplicationContext::setContainer($container);

        return $gate;
    }
}
