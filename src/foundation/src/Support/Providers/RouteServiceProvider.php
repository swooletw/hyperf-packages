<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Support\Providers;

use SwooleTW\Hyperf\Router\RouteFileCollector;
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
        $this->app->get(RouteFileCollector::class)
            ->addRouteFiles($this->routes);
    }
}
