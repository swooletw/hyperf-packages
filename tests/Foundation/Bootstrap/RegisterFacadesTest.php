<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Foundation\Bootstrap;

use Hyperf\Contract\ConfigInterface;
use Mockery as m;
use SwooleTW\Hyperf\Foundation\Bootstrap\RegisterFacades;
use SwooleTW\Hyperf\Tests\Foundation\Concerns\HasMockedApplication;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RegisterFacadesTest extends TestCase
{
    use HasMockedApplication;

    public function testRegisterAliases()
    {
        $config = m::mock(ConfigInterface::class);
        $config->shouldReceive('get')
            ->with('app.aliases', [])
            ->once()
            ->andReturn([
                'FooAlias' => 'FooClass',
            ]);

        $app = $this->getApplication([
            ConfigInterface::class => fn () => $config,
        ]);

        $bootstrapper = $this->createPartialMock(
            RegisterFacades::class,
            ['registerAlias']
        );
        $bootstrapper->expects($this->once())
            ->method('registerAlias')
            ->with('FooClass', 'FooAlias');

        $bootstrapper->bootstrap($app);
    }
}
