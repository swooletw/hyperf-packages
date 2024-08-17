<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Support\Providers;

use Closure;
use RuntimeException;
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
            ->setRouteFiles($this->routes);
    }

    protected function registerRouteFile(string $routeFile): Closure
    {
        if (! file_exists($routeFile)) {
            throw new RuntimeException("Route file does not exist at path `{$routeFile}`.");
        }

        return fn () => require $routeFile;
    }
}
