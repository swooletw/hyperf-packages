<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Bootstrap;

use SwooleTW\Hyperf\Foundation\Contracts\Application as ApplicationContract;

class BootProviders
{
    /**
     * Register App Providers.
     */
    public function bootstrap(ApplicationContract $app): void
    {
        $app->boot();
    }
}
