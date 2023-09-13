<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use FastRoute\DataGenerator as DataGeneratorContract;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\RouteParser as RouteParserContract;
use FastRoute\RouteParser\Std as RouterParser;
use Hyperf\HttpServer\Router\DispatcherFactory as HyperfDispatcherFactory;
use Hyperf\HttpServer\Router\RouteCollector;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                HyperfDispatcherFactory::class => DispatcherFactory::class,
                RouteParserContract::class => RouterParser::class,
                DataGeneratorContract::class => DataGenerator::class,
                RouteCollector::class => NamedRouteCollector::class,
            ],
        ];
    }
}
