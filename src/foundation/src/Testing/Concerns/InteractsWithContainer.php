<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Testing\Concerns;

use Closure;
use Hyperf\Context\ApplicationContext;
use Hyperf\Dispatcher\HttpDispatcher;
use Mockery;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Container\Container;
use SwooleTW\Hyperf\Container\DefinitionSourceFactory;
use SwooleTW\Hyperf\Foundation\ProvidersLoader;
use SwooleTW\Hyperf\Foundation\Testing\Dispatcher\HttpDispatcher as TestingHttpDispatcher;

trait InteractsWithContainer
{
    protected ?ContainerInterface $app = null;

    /**
     * Register an instance of an object in the container.
     *
     * @param string $abstract
     * @param object $instance
     * @return object
     */
    protected function swap($abstract, $instance)
    {
        return $this->instance($abstract, $instance);
    }

    /**
     * Register an instance of an object in the container.
     *
     * @param string $abstract
     * @param object $instance
     * @return object
     */
    protected function instance($abstract, $instance)
    {
        /* @phpstan-ignore-next-line */
        $this->app->set($abstract, $instance);

        return $instance;
    }

    /**
     * Mock an instance of an object in the container.
     *
     * @param string $abstract
     * @return \Mockery\MockInterface
     */
    protected function mock($abstract, Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args())));
    }

    /**
     * Mock a partial instance of an object in the container.
     *
     * @param string $abstract
     * @return \Mockery\MockInterface
     */
    protected function partialMock($abstract, Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args()))->makePartial());
    }

    /**
     * Spy an instance of an object in the container.
     *
     * @param string $abstract
     * @return \Mockery\MockInterface
     */
    protected function spy($abstract, Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::spy(...array_filter(func_get_args())));
    }

    protected function refreshApplication(): void
    {
        ApplicationContext::setContainer(
            $container = $this->createApplication()
        );
        (new ProvidersLoader($container))
            ->load();
        /* @phpstan-ignore-next-line */
        $container->define(HttpDispatcher::class, TestingHttpDispatcher::class);
        $container->get(\Hyperf\Contract\ApplicationInterface::class);

        $this->app = $container;
    }

    protected function createApplication(): ContainerInterface
    {
        return new Container((new DefinitionSourceFactory())());
    }
}
