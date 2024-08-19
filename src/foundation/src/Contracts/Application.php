<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Contracts;

use RuntimeException;
use SwooleTW\Hyperf\Container\Contracts\Container;
use SwooleTW\Hyperf\Support\ServiceProvider;

interface Application extends Container
{
    /**
     * Get the version number of the application.
     */
    public function version(): string;

    /**
     * Run the given array of bootstrap classes.
     */
    public function bootstrapWith(array $bootstrappers): void;

    /**
     * Determine if the application has been bootstrapped before.
     */
    public function hasBeenBootstrapped(): bool;

    /**
     * Set the base path for the application.
     *
     * @return $this
     */
    public function setBasePath(string $basePath): static;

    /**
     * Get the base path of the Laravel installation.
     */
    public function basePath(string $path = ''): string;

    /**
     * Get the path to the application "app" directory.
     */
    public function path(string $path = ''): string;

    /**
     * Get or check the current application environment.
     *
     * @param array|string ...$environments
     */
    public function environment(...$environments): bool|string;

    /**
     * Determine if the application is in the local environment.
     */
    public function isLocal(): bool;

    /**
     * Determine if the application is in the production environment.
     */
    public function isProduction(): bool;

    /**
     * Detect the application's current environment.
     */
    public function detectEnvironment(): string;

    /**
     * Determine if the application is running unit tests.
     */
    public function runningUnitTests(): bool;

    /**
     * Determine if the application is running with debug mode enabled.
     */
    public function hasDebugModeEnabled(): bool;

    /**
     * Register a service provider with the application.
     */
    public function register(ServiceProvider|string $provider, bool $force = false): ServiceProvider;

    /**
     * Get the registered service provider instances if any exist.
     */
    public function getProviders(ServiceProvider|string $provider): array;

    /**
     * Resolve a service provider instance from the class name.
     */
    public function resolveProvider(string $provider): ServiceProvider;

    /**
     * Determine if the application has booted.
     */
    public function isBooted(): bool;

    /**
     * Boot the application's service providers.
     */
    public function boot(): void;

    /**
     * Get the service providers that have been loaded.
     *
     * @return array<string, bool>
     */
    public function getLoadedProviders(): array;

    /**
     * Determine if the given service provider is loaded.
     */
    public function providerIsLoaded(string $provider): bool;

    /**
     * Get the current application locale.
     */
    public function getLocale(): string;

    /**
     * Get the current application locale.
     */
    public function currentLocale(): string;

    /**
     * Get the current application fallback locale.
     */
    public function getFallbackLocale(): string;

    /**
     * Set the current application locale.
     */
    public function setLocale(string $locale): void;

    /**
     * Get the application namespace.
     *
     * @throws RuntimeException
     */
    public function getNamespace(): string;
}
