<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Providers;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\HttpServer\Request;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Database\Connectors\SQLiteConnector;
use SwooleTW\Hyperf\Foundation\Macros\RequestMacro;
use SwooleTW\Hyperf\Support\ServiceProvider;

class FoundationServiceProvider extends ServiceProvider
{
    protected ConfigInterface $config;

    public function __construct(
        protected ContainerInterface $app
    ) {
        $this->config = $app->get(ConfigInterface::class);
    }

    public function boot(): void
    {
        $this->setDatabaseConnection();
        $this->mixinMacros();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerBaseBindings();
        $this->registerContainerAliases();
        $this->registerConnection();
    }

    protected function registerBaseBindings(): void
    {
        $this->app->instance(\Psr\Container\ContainerInterface::class, $this->app);
    }

    protected function registerConnection(): void
    {
        $this->app->bind('db.connector.sqlite', SQLiteConnector::class);
    }

    protected function registerContainerAliases(): void
    {
        foreach ([
            \Psr\Container\ContainerInterface::class => [
                'app',
                \Hyperf\Di\Container::class,
                \Hyperf\Contract\ContainerInterface::class,
                \SwooleTW\Hyperf\Container\Contracts\Container::class,
                \SwooleTW\Hyperf\Container\Container::class,
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
                $this->app->alias($key, $alias);
            }
        }
    }

    protected function setDatabaseConnection(): void
    {
        $connection = $this->config->get('databases.connection', 'default');
        $this->app->get(ConnectionResolverInterface::class)
            ->setDefaultConnection($connection);
    }

    protected function mixinMacros(): void
    {
        Request::mixin(new RequestMacro());
    }
}
