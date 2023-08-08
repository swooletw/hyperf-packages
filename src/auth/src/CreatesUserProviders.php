<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth;

use Hyperf\Database\ConnectionResolverInterface;
use InvalidArgumentException;
use SwooleTW\Hyperf\Auth\Contracts\UserProvider;
use SwooleTW\Hyperf\Auth\Providers\DatabaseUserProvider;
use SwooleTW\Hyperf\Auth\Providers\EloquentUserProvider;
use SwooleTW\Hyperf\Hashing\Contracts\Hasher as HashContract;

trait CreatesUserProviders
{
    /**
     * The registered custom provider creators.
     *
     * @var array
     */
    protected array $customProviderCreators = [];

    /**
     * Create the user provider implementation for the driver.
     *
     * @param  string|null  $provider
     * @return \SwooleTW\Hyperf\Auth\Contracts\UserProvider|null
     *
     * @throws \InvalidArgumentException
     */
    public function createUserProvider(?string $provider = null): ?UserProvider
    {
        if (is_null($config = $this->getProviderConfiguration($provider))) {
            return null;
        }

        if (isset($this->customProviderCreators[$driver = ($config['driver'] ?? null)])) {
            return call_user_func(
                $this->customProviderCreators[$driver], $this->app, $config
            );
        }

        return match ($driver) {
            'database' => $this->createDatabaseProvider($config),
            'eloquent' => $this->createEloquentProvider($config),
            default => throw new InvalidArgumentException(
                "Authentication user provider [{$driver}] is not defined."
            ),
        };
    }

    /**
     * Get the user provider configuration.
     *
     * @param  string|null  $provider
     * @return array|null
     */
    protected function getProviderConfiguration($provider): ?array
    {
        if ($provider = $provider ?: $this->getDefaultUserProvider()) {
            return $this->config->get("auth.providers.{$provider}");
        }

        return null;
    }

    /**
     * Create an instance of the database user provider.
     *
     * @param  array  $config
     * @return \SwooleTW\Hyperf\Auth\Providers\DatabaseUserProvider
     */
    protected function createDatabaseProvider(array $config): DatabaseUserProvider
    {
        $connection = $this->app->make(ConnectionResolverInterface::class)
            ->connection($config['connection'] ?? null);

        return new DatabaseUserProvider(
            $connection,
            $this->app->make(HashContract::class),
            $config['table']
        );
    }


    /**
     * Create an instance of the Eloquent user provider.
     *
     * @param  array  $config
     * @return \SwooleTW\Hyperf\Auth\Providers\EloquentUserProvider
     */
    protected function createEloquentProvider(array $config): EloquentUserProvider
    {
        return new EloquentUserProvider(
            $this->app->make(HashContract::class),
            $config['model']
        );
    }

    /**
     * Get the default user provider name.
     *
     * @return string
     */
    public function getDefaultUserProvider(): string
    {
        return $this->config->get('auth.defaults.provider');
    }
}
