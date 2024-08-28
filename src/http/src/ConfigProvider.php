<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Http;

use SwooleTW\Hyperf\Http\Listeners\MixinRequestMacros;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                MixinRequestMacros::class,
            ],
        ];
    }
}
