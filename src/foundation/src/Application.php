<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation;

use Closure;
use Hyperf\Collection\Arr;
use Hyperf\Di\Definition\DefinitionSourceInterface;
use Hyperf\Macroable\Macroable;
use Psr\Container\ContainerInterface;
use RuntimeException;
use SwooleTW\Hyperf\Container\Container;
use SwooleTW\Hyperf\Container\DefinitionSourceFactory;
use SwooleTW\Hyperf\Foundation\Contracts\Application as ApplicationContract;
use SwooleTW\Hyperf\Foundation\Events\LocaleUpdated;
use SwooleTW\Hyperf\Support\Environment;
use SwooleTW\Hyperf\Support\ServiceProvider;

use function Hyperf\Collection\data_get;

class Application extends Container implements ApplicationContract
{
    use Macroable;

    /**
     * The Laravel Hyperf framework version.
     *
     * @var string
     */
    public const VERSION = '0.1.0';

    /**
     * The base path for the Laravel Hyperf installation.
     */
    protected string $basePath;

    /**
     * Indicates if the application has been bootstrapped before.
     */
    protected bool $hasBeenBootstrapped = false;

    /**
     * Indicates if the application has "booted".
     */
    protected bool $booted = false;

    /**
     * All of the registered service providers.
     *
     * @var array<string, ServiceProvider>
     */
    protected array $serviceProviders = [];

    /**
     * The names of the loaded service providers.
     */
    protected array $loadedProviders = [];

    /**
     * The application namespace.
     */
    protected ?string $namespace;

    public function __construct(?DefinitionSourceInterface $definitionSource = null, ?string $basePath = null)
    {
        $this->setBasePath($basePath ?: BASE_PATH);

        parent::__construct($definitionSource ?: $this->getDefinitionSource());

        $this->registerBaseBindings();
        $this->registerCoreContainerAliases();
    }

    protected function getDefinitionSource(): DefinitionSourceInterface
    {
        return (new DefinitionSourceFactory())();
    }

    /**
     * Get the version number of the application.
     */
    public function version(): string
    {
        return static::VERSION;
    }

    /**
     * Register the basic bindings into the container.
     */
    protected function registerBaseBindings(): void
    {
        $this->instance(ContainerInterface::class, $this);
    }

    /**
     * Run the given array of bootstrap classes.
     *
     * @param string[] $bootstrappers
     */
    public function bootstrapWith(array $bootstrappers): void
    {
        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            $this['events']->dispatch('bootstrapping: ' . $bootstrapper, [$this]);

            $this->make($bootstrapper)->bootstrap($this);

            $this['events']->dispatch('bootstrapped: ' . $bootstrapper, [$this]);
        }
    }

    /**
     * Register a callback to run before a bootstrapper.
     */
    public function beforeBootstrapping(string $bootstrapper, Closure $callback): void
    {
        $this['events']->listen('bootstrapping: ' . $bootstrapper, $callback);
    }

    /**
     * Register a callback to run after a bootstrapper.
     */
    public function afterBootstrapping(string $bootstrapper, Closure $callback): void
    {
        $this['events']->listen('bootstrapped: ' . $bootstrapper, $callback);
    }

    /**
     * Determine if the application has been bootstrapped before.
     */
    public function hasBeenBootstrapped(): bool
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * Set the base path for the application.
     *
     * @return $this
     */
    public function setBasePath(string $basePath): static
    {
        $this->basePath = rtrim($basePath, '\/');

        return $this;
    }

    /**
     * Get the path to the application "app" directory.
     */
    public function path(string $path = ''): string
    {
        if (empty($path)) {
            return $this->basePath('app');
        }

        return $this->basePath('app' . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR));
    }

    /**
     * Get the base path of the Laravel installation.
     */
    public function basePath(string $path = ''): string
    {
        if (empty($path)) {
            return $this->basePath;
        }

        return $this->basePath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Get or check the current application environment.
     *
     * @param array|string ...$environments
     */
    public function environment(...$environments): bool|string
    {
        if (count($environments) > 0) {
            return $this->get(Environment::class)->is(...$environments);
        }

        return $this->detectEnvironment();
    }

    /**
     * Determine if the application is in the local environment.
     */
    public function isLocal(): bool
    {
        return $this->get(Environment::class)->is('local');
    }

    /**
     * Determine if the application is in the production environment.
     */
    public function isProduction(): bool
    {
        return $this->get(Environment::class)->is('production');
    }

    /**
     * Detect the application's current environment.
     */
    public function detectEnvironment(): string
    {
        return $this->get(Environment::class)->get();
    }

    /**
     * Determine if the application is running unit tests.
     */
    public function runningUnitTests(): bool
    {
        return $this->get(Environment::class)->is('testing');
    }

    /**
     * Determine if the application is running with debug mode enabled.
     */
    public function hasDebugModeEnabled(): bool
    {
        return $this->get(Environment::class)->isDebug();
    }

    /**
     * Register a service provider with the application.
     */
    public function register(ServiceProvider|string $provider, bool $force = false): ServiceProvider
    {
        if (($registered = $this->getProvider($provider)) && ! $force) {
            return $registered;
        }

        // If the given "provider" is a string, we will resolve it, passing in the
        // application instance automatically for the developer. This is simply
        // a more convenient way of specifying your service provider classes.
        if (is_string($provider)) {
            $provider = $this->resolveProvider($provider);
        }

        $provider->register();

        // If there are bindings set as properties on the provider we
        // will spin through them and register them with the application, which
        // serves as a convenience layer while registering a lot of bindings.
        if (property_exists($provider, 'bindings')) {
            foreach ($provider->bindings as $key => $value) {
                $this->bind($key, $value);
            }
        }

        $this->markAsRegistered($provider);

        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by this developer's application logic.
        if ($this->isBooted()) {
            $this->bootProvider($provider);
        }

        return $provider;
    }

    /**
     * Get the registered service provider instance if it exists.
     */
    public function getProvider(ServiceProvider|string $provider): ?ServiceProvider
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return $this->serviceProviders[$name] ?? null;
    }

    /**
     * Get the registered service provider instances if any exist.
     */
    public function getProviders(ServiceProvider|string $provider): array
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return Arr::where($this->serviceProviders, fn ($value) => $value instanceof $name);
    }

    /**
     * Resolve a service provider instance from the class name.
     */
    public function resolveProvider(string $provider): ServiceProvider
    {
        return new $provider($this);
    }

    /**
     * Mark the given provider as registered.
     */
    protected function markAsRegistered(ServiceProvider $provider): void
    {
        $class = get_class($provider);

        $this->serviceProviders[$class] = $provider;

        $this->loadedProviders[$class] = true;
    }

    /**
     * Determine if the application has booted.
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Boot the application's service providers.
     */
    public function boot(): void
    {
        if ($this->isBooted()) {
            return;
        }

        array_walk($this->serviceProviders, function ($p) {
            $this->bootProvider($p);
        });

        $this->booted = true;
    }

    /**
     * Boot the given service provider.
     */
    protected function bootProvider(ServiceProvider $provider): void
    {
        $provider->callBootingCallbacks();

        if (method_exists($provider, 'boot')) {
            $this->call([$provider, 'boot']);
        }

        $provider->callBootedCallbacks();
    }

    /**
     * Get the service providers that have been loaded.
     *
     * @return array<string, bool>
     */
    public function getLoadedProviders(): array
    {
        return $this->loadedProviders;
    }

    /**
     * Determine if the given service provider is loaded.
     */
    public function providerIsLoaded(string $provider): bool
    {
        return isset($this->loadedProviders[$provider]);
    }

    /**
     * Get the current application locale.
     */
    public function getLocale(): string
    {
        return $this['config']->get('app.locale');
    }

    /**
     * Get the current application locale.
     */
    public function currentLocale(): string
    {
        return $this->getLocale();
    }

    /**
     * Get the current application fallback locale.
     */
    public function getFallbackLocale(): string
    {
        return $this['config']->get('app.fallback_locale');
    }

    /**
     * Set the current application locale.
     */
    public function setLocale(string $locale): void
    {
        $this['config']->set('app.locale', $locale);

        $this['translator']->setLocale($locale);

        $this['events']->dispatch(new LocaleUpdated($locale));
    }

    /**
     * Register the core class aliases in the container.
     */
    protected function registerCoreContainerAliases(): void
    {
        foreach ([
            \Psr\Container\ContainerInterface::class => [
                'app',
                \Hyperf\Di\Container::class,
                \Hyperf\Contract\ContainerInterface::class,
                \SwooleTW\Hyperf\Container\Contracts\Container::class,
                \SwooleTW\Hyperf\Container\Container::class,
                \SwooleTW\Hyperf\Foundation\Contracts\Application::class,
                \SwooleTW\Hyperf\Foundation\Application::class,
            ],
            \Hyperf\Contract\ConfigInterface::class => ['config'],
            \Psr\EventDispatcher\EventDispatcherInterface::class => ['events'],
            \Hyperf\HttpServer\Router\DispatcherFactory::class => ['router'],
            \Hyperf\Contract\StdoutLoggerInterface::class => ['log'],
            \SwooleTW\Hyperf\Encryption\Encrypter::class => ['encrypt'],
            \SwooleTW\Hyperf\Cache\Contracts\Factory::class => [
                'cache',
                \SwooleTW\Hyperf\Cache\CacheManager::class,
            ],
            \SwooleTW\Hyperf\Cache\Contracts\Store::class => [
                'cache.store',
                \SwooleTW\Hyperf\Cache\Repository::class,
            ],
            \League\Flysystem\Filesystem::class => ['files'],
            \Hyperf\Contract\TranslatorInterface::class => ['translator'],
            \Hyperf\Validation\Contract\ValidatorFactoryInterface::class => ['validator'],
            \Hyperf\HttpServer\Contract\RequestInterface::class => ['request'],
            \Hyperf\HttpServer\Contract\ResponseInterface::class => ['response'],
            \Hyperf\DbConnection\Db::class => ['db'],
            \SwooleTW\Hyperf\Auth\Contracts\FactoryContract::class => [
                'auth',
                \SwooleTW\Hyperf\Auth\AuthManager::class,
            ],
            \SwooleTW\Hyperf\Auth\Contracts\Guard::class => [
                'auth.driver',
            ],
            \SwooleTW\Hyperf\Hashing\Contracts\Hasher::class => ['hash'],
            \SwooleTW\Hyperf\Cookie\CookieManager::class => ['cookie'],
            \SwooleTW\Hyperf\Auth\Contracts\FactoryContract::class => [
                'auth',
                \SwooleTW\Hyperf\Auth\AuthManager::class,
            ],
            \SwooleTW\Hyperf\JWT\Contracts\ManagerContract::class => [
                'jwt',
                \SwooleTW\Hyperf\JWT\JWTManager::class,
            ],
        ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }

    /**
     * Get the application namespace.
     *
     * @throws RuntimeException
     */
    public function getNamespace(): string
    {
        if (isset($this->namespace)) {
            return $this->namespace;
        }

        $composer = json_decode(file_get_contents($this->basePath('composer.json')), true);
        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath($this->path()) === realpath($this->basePath($pathChoice))) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new RuntimeException('Unable to detect application namespace.');
    }
}
