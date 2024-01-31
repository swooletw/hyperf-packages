<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Watcher;

use Hyperf\Watcher\Watcher as HyperfWatcher;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                HyperfWatcher::class => Watcher::class,
            ],
        ];
    }
}
