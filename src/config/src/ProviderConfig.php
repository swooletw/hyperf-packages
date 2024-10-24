<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Config;

use Hyperf\Collection\Arr;
use Hyperf\Config\ProviderConfig as HyperfProviderConfig;
use Hyperf\Support\Composer;

/**
 * Provider config allow the components set the configs to application.
 */
class ProviderConfig extends HyperfProviderConfig
{
    protected static array $providerConfigs = [];

    /**
     * Load and merge all provider configs from components.
     * Notice that this method will cached the config result into a static property,
     * call ProviderConfig::clear() method if you want to reset the static property.
     */
    public static function load(): array
    {
        if (static::$providerConfigs) {
            return static::$providerConfigs;
        }

        $packagesToIgnore = Composer::getMergedExtra('hyperf')['dont-discover'] ?? [];
        if (in_array('*', $packagesToIgnore)) {
            return static::$providerConfigs = [];
        }

        $providers = array_map(
            fn (array $package) => Arr::wrap(($package['hyperf']['config'] ?? []) ?? []),
            Composer::getMergedExtra()
        );
        $providers = array_filter(
            $providers,
            fn ($package) => ! in_array($package, $packagesToIgnore),
            ARRAY_FILTER_USE_KEY
        );

        return static::$providerConfigs = static::loadProviders(
            Arr::flatten($providers)
        );
    }
}
