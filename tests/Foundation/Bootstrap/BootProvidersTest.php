<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Foundation\Bootstrap;

use SwooleTW\Hyperf\Foundation\Bootstrap\BootProviders;
use SwooleTW\Hyperf\Support\ServiceProvider;
use SwooleTW\Hyperf\Tests\Foundation\Concerns\HasMockedApplication;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class BootProvidersTest extends TestCase
{
    use HasMockedApplication;

    public function testBoot()
    {
        $app = $this->getApplication();
        $app->register(ApplicationBasicServiceProviderStub::class);

        (new BootProviders())->bootstrap($app);

        $this->assertSame('bar', $app->get('foo'));
    }
}

class ApplicationBasicServiceProviderStub extends ServiceProvider
{
    public function boot()
    {
        $this->app->bind('foo', fn () => 'bar');
    }
}
