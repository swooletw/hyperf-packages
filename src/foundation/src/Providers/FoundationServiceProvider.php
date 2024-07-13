<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Providers;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\HttpServer\Request;
use Psr\Container\ContainerInterface;
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
        $this->overrideHyperfConfigs();
        $this->setDatabaseConnection();
        $this->mixinMacros();
    }

    /**
     * Register any application services.
     */
    public function register(): void {}

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

    protected function overrideHyperfConfigs(): void
    {
        $configs = [
            'app_name' => $this->config->get('app.name'),
            'app_env' => $this->config->get('app.env'),
            'scan_cacheable' => $this->config->get('app.scan_cacheable'),
            StdoutLoggerInterface::class . '.log_level' => $this->config->get('app.stdout_log_level'),
        ];

        foreach ($configs as $key => $value) {
            $this->config->set($key, $value);
        }
    }
}
