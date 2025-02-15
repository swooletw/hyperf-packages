<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Telescope;

use SwooleTW\Hyperf\Telescope\Aspects\GuzzleHttpClientAspect;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'aspects' => [
                GuzzleHttpClientAspect::class,
            ],
        ];
    }
}
