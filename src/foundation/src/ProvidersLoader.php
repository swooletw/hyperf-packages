<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation;

use Hyperf\Contract\ApplicationInterface;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Support\ServiceProvider;

class ProvidersLoader
{
    protected array $bootstrappers = [
        \SwooleTW\Hyperf\Foundation\Bootstrap\LoadAliases::class,
        \SwooleTW\Hyperf\Foundation\Bootstrap\LoadCommands::class,
        \SwooleTW\Hyperf\Foundation\Bootstrap\LoadCrontabs::class,
    ];

    public function __construct(
        protected ContainerInterface $container,
        protected array $providers = []
    ) {
        $this->container = $container;
    }

    public function load(): void
    {
        // bootstrappers are before any other packages
        $this->bootstrap();

        // after initiating application, all the service bindings will
        // be prepared to the app container
        $this->container->get(ApplicationInterface::class);

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

    protected function bootstrap(): void
    {
        foreach ($this->bootstrappers as $bootstrapper) {
            (new $bootstrapper())
                ->bootstrap($this->container);
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
