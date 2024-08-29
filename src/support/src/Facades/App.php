<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Closure;
use SwooleTW\Hyperf\Foundation\Application;
use SwooleTW\Hyperf\Support\ServiceProvider;

/**
 * @method static string version()
 * @method static void bootstrapWith(array $bootstrappers)
 * @method static void beforeBootstrapping(string $bootstrapper, Closure $callback)
 * @method static void afterBootstrapping(string $bootstrapper, Closure $callback)
 * @method static bool hasBeenBootstrapped()
 * @method static string basePath(string $path = '')
 * @method static string path(string $path = '')
 * @method static bool|string environment(...$environments)
 * @method static bool isLocal()
 * @method static bool isProduction()
 * @method static string detectEnvironment()
 * @method static bool runningUnitTests()
 * @method static bool hasDebugModeEnabled()
 * @method static ServiceProvider register(ServiceProvider|string $provider, bool $force = false)
 * @method static ServiceProvider|null getProvider(ServiceProvider|string $provider)
 * @method static array getProviders(ServiceProvider|string $provider)
 * @method static bool isBooted()
 * @method static void boot()
 * @method static array getLoadedProviders()
 * @method static bool providerIsLoaded(string $provider)
 * @method static string getLocale()
 * @method static string currentLocale()
 * @method static string getFallbackLocale()
 * @method static void setLocale(string $locale)
 * @method static string getNamespace()
 * @method static mixed make(string $abstract, array $parameters = [])
 * @method static mixed get(string $id)
 * @method static bool has(string $id)
 * @method static void bind(string $abstract, $concrete = null, bool $shared = false)
 * @method static void singleton(string $abstract, $concrete = null)
 * @method static void instance(string $abstract, $instance)
 * @method static mixed call($callback, array $parameters = [], $defaultMethod = null)
 * @method static void alias(string $abstract, string $alias)
 * @method static bool bound(string $abstract)
 * @method static array getBindings()
 * @method static mixed makeWith(string $abstract, array $parameters = [])
 * @method static void beforeResolving(string $abstract, Closure $callback = null)
 * @method static void resolving(string $abstract, Closure $callback = null)
 * @method static void afterResolving(string $abstract, Closure $callback = null)
 * @method static string getAlias(string $abstract)
 * @method static void forgetExtenders(string $abstract)
 * @method static void forgetInstance(string $abstract)
 * @method static void forgetInstances()
 * @method static array getExtenders(string $abstract)
 * @method static array tagged(string $tag)
 * @method static void tag(array|string $abstracts, array|string $tags)
 *
 * @see Application
 */
class App extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'app';
    }
}
