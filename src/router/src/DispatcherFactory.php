<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\Router\DispatcherFactory as BaseDispatcherFactory;
use Hyperf\HttpServer\Router\RouteCollector;

class DispatcherFactory extends BaseDispatcherFactory
{
    protected array $routes = [
        BASE_PATH . '/routes/web.php',
        BASE_PATH . '/routes/api.php',
    ];

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct();
    }

    public function getRouter(string $serverName): RouteCollector
    {
        if (isset($this->routers[$serverName])) {
            return $this->routers[$serverName];
        }

        return $this->routers[$serverName] = $this->container->make(RouteCollector::class, ['server' => $serverName]);
    }
}
