<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Foundation\Bootstrap;

use Hyperf\Contract\ConfigInterface;
use Mockery as m;
use SwooleTW\Hyperf\Foundation\Bootstrap\RegisterProviders;
use SwooleTW\Hyperf\Foundation\Support\Composer;
use SwooleTW\Hyperf\Support\ServiceProvider;
use SwooleTW\Hyperf\Tests\Foundation\Concerns\HasMockedApplication;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RegisterProvidersTest extends TestCase
{
    use HasMockedApplication;

    public function testRegisterProviders()
    {
        $config = m::mock(ConfigInterface::class);
        $config->shouldReceive('get')
            ->with('app.providers', [])
            ->once()
            ->andReturn([
                TestTwoServiceProvider::class,
            ]);

        $app = $this->getApplication([
            ConfigInterface::class => fn () => $config,
        ]);

        Composer::setBasePath(__DIR__ . '/../fixtures/hyperf1');

        (new RegisterProviders())->bootstrap($app);

        // reset base path, otherwise it will affect other tests
        Composer::setBasePath(null);

        $this->assertSame('foo', $app->get('foo'));
        $this->assertSame('bar', $app->get('bar'));
    }
}

class TestOneServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('foo', function () {
            return 'foo';
        });
    }
}

class TestTwoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('bar', function () {
            return 'bar';
        });
    }
}
