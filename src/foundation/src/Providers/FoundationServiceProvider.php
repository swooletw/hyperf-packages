<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Providers;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Request;
use SwooleTW\Hyperf\Foundation\Contracts\Application as ApplicationContract;
use SwooleTW\Hyperf\Foundation\Http\Contracts\MiddlewareContract;
use SwooleTW\Hyperf\Foundation\Macros\RequestMacro;
use SwooleTW\Hyperf\Support\ServiceProvider;

class FoundationServiceProvider extends ServiceProvider
{
    protected ConfigInterface $config;

    public function __construct(
        protected ApplicationContract $app
    ) {
        $this->config = $app->get(ConfigInterface::class);
    }

    public function boot(): void
    {
        $this->setDefaultTimezone();
        $this->setInternalEncoding();
        $this->setDatabaseConnection();
        $this->mixinMacros();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->callAfterResolving(ConfigInterface::class, function (ConfigInterface $config) {
            $this->config = $config;
            $this->overrideHyperfConfigs();
        });
    }

    protected function setDatabaseConnection(): void
    {
        $connection = $this->config->get('database.default', 'mysql');
        $this->app->get(ConnectionResolverInterface::class)
            ->setDefaultConnection($connection);
    }

    protected function mixinMacros(): void
    {
        Request::mixin(new RequestMacro());
    }

    protected function overrideHyperfConfigs(): void
    {
        $configs = [
            'app_name' => $this->config->get('app.name'),
            'app_env' => $this->config->get('app.env'),
            'scan_cacheable' => $this->config->get('app.scan_cacheable'),
            StdoutLoggerInterface::class . '.log_level' => $this->config->get('app.stdout_log_level'),
            'translation.locale' => $this->config->get('app.locale'),
            'translation.fallback_locale' => $this->config->get('app.fallback_locale'),
            'translation.path' => base_path('lang'),
            'databases' => $this->config->get('database.connections'),
            'databases.migrations' => $migration = $this->config->get('database.migrations', 'migrations'),
            'databases.default.migrations' => $migration,
            'redis' => $this->getRedisConfig(),
        ];

        foreach ($configs as $key => $value) {
            if (! $this->config->has($key)) {
                $this->config->set($key, $value);
            }
        }

        $this->config->set('middlewares', $this->getMiddlewareConfig());
    }

    protected function getRedisConfig(): array
    {
        $redisConfig = $this->config->get('database.redis', []);
        $redisOptions = $redisConfig['options'] ?? [];
        unset($redisConfig['options']);

        return array_map(function (array $config) use ($redisOptions) {
            return array_merge($config, [
                'options' => $redisOptions,
            ]);
        }, $redisConfig);
    }

    protected function getMiddlewareConfig(): array
    {
        if ($middleware = $this->config->get('middlewares', [])) {
            foreach ($middleware as $server => $middlewareConfig) {
                $middleware[$server] = MiddlewareManager::sortMiddlewares($middlewareConfig);
            }
        }

        foreach ($this->config->get('server.kernels') as $server => $kernel) {
            if (! is_string($kernel) || ! is_a($kernel, MiddlewareContract::class, true)) {
                continue;
            }
            $middleware[$server] = array_merge(
                $this->app->get($kernel)->getGlobalMiddleware(),
                $middleware[$server] ?? [],
            );
        }

        return $middleware;
    }

    protected function setDefaultTimezone(): void
    {
        date_default_timezone_set($this->config->get('app.timezone', 'UTC'));
    }

    protected function setInternalEncoding(): void
    {
        mb_internal_encoding('UTF-8');
    }
}
