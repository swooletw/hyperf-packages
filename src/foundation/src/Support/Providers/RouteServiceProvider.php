<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Support\Providers;

use SwooleTW\Hyperf\Router\DispatcherFactory;
use SwooleTW\Hyperf\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The route files for the application.
     */
    protected array $routes = [
    ];

    public function boot(): void
    {
        DispatcherFactory::setRouteFiles($this->routes);
    }
}
