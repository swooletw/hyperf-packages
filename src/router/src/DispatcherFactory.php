<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use Hyperf\HttpServer\Router\DispatcherFactory as BaseDispatcherFactory;

class DispatcherFactory extends BaseDispatcherFactory
{
    protected array $routes = [
        BASE_PATH . '/routes/web.php',
        BASE_PATH . '/routes/api.php',
    ];
}
