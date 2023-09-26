<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Dispatcher;

use Hyperf\Dispatcher\HttpRequestHandler;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'annotations' => [
                'scan' => [
                    'class_map' => [
                        HttpRequestHandler::class => __DIR__ . '/../class_map/HttpRequestHandler.php',
                    ],
                ],
            ],
        ];
    }
}
