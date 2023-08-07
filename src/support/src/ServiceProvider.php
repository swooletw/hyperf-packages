<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support;

use Psr\Container\ContainerInterface;

abstract class ServiceProvider
{
    public function __construct(
        protected ContainerInterface $app
    ) {}

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }
}
