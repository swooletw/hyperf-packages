<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Bootstrap;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Application as ApplicationContract;
use SwooleTW\Hyperf\Support\ServiceProvider;

class RegisterProviders
{
    protected ?ContainerInterface $container = null;

    protected array $providers = [];

    /**
     * Register Class Aliases.
     */
    public function bootstrap(ApplicationContract $app): void
    {
        $this->container = $app->getContainer();

        $providers = $this->getServiceProviders();
        foreach ($providers as $providerClass) {
            $provider = $this->getProvider($providerClass);

            if (! $provider instanceof ServiceProvider) {
                continue;
            }

            $provider->register();

            if (! method_exists($provider, 'boot')) {
                continue;
            }
            $provider->boot();
        }
    }

    protected function getProvider(string $providerClass): ServiceProvider
    {
        if ($provider = $this->providers[$providerClass] ?? null) {
            return $provider;
        }

        return $this->providers[$providerClass] = new $providerClass($this->container);
    }

    protected function getServiceProviders(): array
    {
        return $this->container
            ->get(ConfigInterface::class)
            ->get('app.providers', []);
    }
}
