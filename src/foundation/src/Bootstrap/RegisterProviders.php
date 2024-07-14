<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Bootstrap;

use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Support\Composer;
use SwooleTW\Hyperf\Foundation\Contracts\Application as ApplicationContract;

class RegisterProviders
{
    /**
     * Register App Providers.
     */
    public function bootstrap(ApplicationContract $app): void
    {
        $providers = array_map(
            fn (array $package) => Arr::wrap(($package['hyperf']['providers'] ?? []) ?? []),
            Composer::getMergedExtra()
        );
        $providers = array_unique(
            array_merge(
                Arr::flatten($providers),
                $app->get(ConfigInterface::class)->get('app.providers', [])
            )
        );

        foreach ($providers as $providerClass) {
            $app->register($providerClass);
        }
    }

}
