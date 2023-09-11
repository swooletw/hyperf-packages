<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Routing;

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\RouteParser\Std;
use Hyperf\HttpServer\Router\DispatcherFactory as BaseDispatcherFactory;
use Hyperf\HttpServer\Router\RouteCollector;

class DispatcherFactory extends BaseDispatcherFactory
{
    protected array $routes = [
        BASE_PATH . '/routes/web.php',
        BASE_PATH . '/routes/api.php',
    ];

    public function getRouter(string $serverName): RouteCollector
    {
        if (isset($this->routers[$serverName])) {
            return $this->routers[$serverName];
        }

        $parser = new Std();
        $generator = new DataGenerator();

        return $this->routers[$serverName] = new NamedRouteCollector($parser, $generator, $serverName);
    }
}
