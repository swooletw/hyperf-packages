<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Foundation;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\TranslatorInterface;
use Mockery as m;
use Psr\EventDispatcher\EventDispatcherInterface;
use stdClass;
use SwooleTW\Hyperf\Container\DefinitionSource;
use SwooleTW\Hyperf\Event\EventDispatcher;
use SwooleTW\Hyperf\Event\ListenerProvider;
use SwooleTW\Hyperf\Foundation\Application;
use SwooleTW\Hyperf\Foundation\Bootstrap\RegisterFacades;
use SwooleTW\Hyperf\Foundation\Events\LocaleUpdated;
use SwooleTW\Hyperf\Support\Environment;
use SwooleTW\Hyperf\Support\ServiceProvider;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FoundationApplicationTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testSetLocaleSetsLocaleAndFiresLocaleChangedEvent()
    {
        $config = m::mock(stdClass::class);
        $config->shouldReceive('set')
            ->with('app.locale', 'foo')
            ->once();
        $trans = m::mock(stdClass::class);
        $trans->shouldReceive('setLocale')
            ->with('foo')
            ->once();
        $events = m::mock(stdClass::class);
        $events->shouldReceive('dispatch')
            ->with(m::type(LocaleUpdated::class))
            ->once();

        $app = $this->getApplication([
            ConfigInterface::class => fn () => $config,
            TranslatorInterface::class => fn () => $trans,
            EventDispatcherInterface::class => fn () => $events,
        ]);

        $app->setLocale('foo');

        $this->assertExpectationCount(3);
    }

    public function testServiceProvidersAreCorrectlyRegistered()
    {
        $provider = m::mock(ApplicationBasicServiceProviderStub::class);
        $class = get_class($provider);
        $provider->shouldReceive('register')->once();
        $app = $this->getApplication();
        $app->register($provider);

        $this->assertArrayHasKey($class, $app->getLoadedProviders());
    }

    public function testClassesAreBoundWhenServiceProviderIsRegistered()
    {
        $app = $this->getApplication();
        $app->register($provider = new class($app) extends ServiceProvider {
            public $bindings = [
                AbstractClass::class => ConcreteClass::class,
            ];
        });

        $this->assertArrayHasKey(get_class($provider), $app->getLoadedProviders());

        $instance = $app->make(AbstractClass::class);

        $this->assertInstanceOf(ConcreteClass::class, $instance);
        $this->assertNotSame($instance, $app->make(AbstractClass::class));
    }

    public function testServiceProvidersAreCorrectlyRegisteredWhenRegisterMethodIsNotFilled()
    {
        $provider = m::mock(ServiceProvider::class);
        $class = get_class($provider);
        $provider->shouldReceive('register')->once();
        $app = $this->getApplication();
        $app->register($provider);

        $this->assertArrayHasKey($class, $app->getLoadedProviders());
    }

    public function testServiceProvidersCouldBeLoaded()
    {
        $provider = m::mock(ServiceProvider::class);
        $class = get_class($provider);
        $provider->shouldReceive('register')->once();
        $app = $this->getApplication();
        $app->register($provider);

        $this->assertTrue($app->providerIsLoaded($class));
        $this->assertFalse($app->providerIsLoaded(ApplicationBasicServiceProviderStub::class));
    }

    public function testEnvironment()
    {
        $app = $this->getApplication([
            Environment::class => fn () => new Environment('foo', true),
        ]);

        $this->assertSame('foo', $app->environment());

        $this->assertTrue($app->environment('foo'));
        $this->assertTrue($app->environment('f*'));
        $this->assertTrue($app->environment('foo', 'bar'));
        $this->assertTrue($app->environment(['foo', 'bar']));

        $this->assertFalse($app->environment('qux'));
        $this->assertFalse($app->environment('q*'));
        $this->assertFalse($app->environment('qux', 'bar'));
        $this->assertFalse($app->environment(['qux', 'bar']));
    }

    public function testEnvironmentHelpers()
    {
        $local = $this->getApplication([
            Environment::class => fn () => new Environment('local', true),
        ]);

        $this->assertTrue($local->isLocal());
        $this->assertFalse($local->isProduction());
        $this->assertFalse($local->runningUnitTests());

        $production = $this->getApplication([
            Environment::class => fn () => new Environment('production', true),
        ]);

        $this->assertTrue($production->isProduction());
        $this->assertFalse($production->isLocal());
        $this->assertFalse($production->runningUnitTests());

        $testing = $this->getApplication([
            Environment::class => fn () => new Environment('testing', true),
        ]);

        $this->assertTrue($testing->runningUnitTests());
        $this->assertFalse($testing->isLocal());
        $this->assertFalse($testing->isProduction());
    }

    public function testDebugHelper()
    {
        $debugOff = $this->getApplication([
            Environment::class => fn () => new Environment('production', false),
        ]);

        $this->assertFalse($debugOff->hasDebugModeEnabled());

        $debugOn = $this->getApplication([
            Environment::class => fn () => new Environment('production', true),
        ]);

        $this->assertTrue($debugOn->hasDebugModeEnabled());
    }

    public function testBeforeBootstrappingAddsClosure()
    {
        $eventDispatcher = new EventDispatcher(
            new ListenerProvider(),
            null,
            $app = $this->getApplication()
        );
        $app->instance(
            EventDispatcherInterface::class,
            $eventDispatcher
        );

        $closure = function () {};
        $app->beforeBootstrapping(RegisterFacades::class, $closure);
        $this->assertArrayHasKey(0, $app['events']->getListeners('bootstrapping: SwooleTW\Hyperf\Foundation\Bootstrap\RegisterFacades'));
    }

    public function testAfterBootstrappingAddsClosure()
    {
        $eventDispatcher = new EventDispatcher(
            new ListenerProvider(),
            null,
            $app = $this->getApplication()
        );
        $app->instance(
            EventDispatcherInterface::class,
            $eventDispatcher
        );

        $closure = function () {};
        $app->afterBootstrapping(RegisterFacades::class, $closure);
        $this->assertArrayHasKey(0, $app['events']->getListeners('bootstrapped: SwooleTW\Hyperf\Foundation\Bootstrap\RegisterFacades'));
    }

    public function testGetNamespace()
    {
        $app1 = $this->getApplication([], realpath(__DIR__ . '/fixtures/hyperf1'));
        $app2 = $this->getApplication([], realpath(__DIR__ . '/fixtures/hyperf2'));

        $this->assertSame('Hyperf\\One\\', $app1->getNamespace());
        $this->assertSame('Hyperf\\Two\\', $app2->getNamespace());
    }

    public function testMacroable()
    {
        $app = $this->getApplication();
        $app->macro('foo', function () {
            return 'bar';
        });

        $this->assertSame('bar', $app->foo());
    }

    protected function getApplication(array $definitionSources = [], string $basePath = 'base_path'): Application
    {
        return new Application(
            new DefinitionSource($definitionSources),
            $basePath
        );
    }

    protected function assertExpectationCount(int $times): void
    {
        $this->assertSame($times, m::getContainer()->mockery_getExpectationCount());
    }
}

class ApplicationBasicServiceProviderStub extends ServiceProvider
{
    public function boot() {}

    public function register(): void {}
}

abstract class AbstractClass {}

class ConcreteClass extends AbstractClass {}
