<?php

declare(strict_types=1);

use Carbon\Carbon;
use FriendsOfHyperf\AsyncTask\Task as AsyncTask;
use FriendsOfHyperf\AsyncTask\TaskInterface as AsyncTaskInterface;
use FriendsOfHyperf\Support\AsyncQueue\ClosureJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\SessionInterface;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\Stringable\Stringable;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use Hyperf\ViewEngine\Contract\ViewInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use SwooleTW\Hyperf\Cookie\Contracts\Cookie as CookieContract;
use SwooleTW\Hyperf\Foundation\Exceptions\Contracts\ExceptionHandler as ExceptionHandlerContract;
use SwooleTW\Hyperf\Http\Contracts\RequestContract;
use SwooleTW\Hyperf\Http\Contracts\ResponseContract;
use SwooleTW\Hyperf\HttpMessage\Exceptions\HttpException;
use SwooleTW\Hyperf\HttpMessage\Exceptions\HttpResponseException;
use SwooleTW\Hyperf\HttpMessage\Exceptions\NotFoundHttpException;
use SwooleTW\Hyperf\Router\UrlGenerator;
use SwooleTW\Hyperf\Support\Contracts\Responsable;

if (! function_exists('abort')) {
    /**
     * Throw an HttpException with the given data.
     *
     * @param int|Responsable $code
     *
     * @throws HttpException
     * @throws NotFoundHttpException
     * @throws HttpResponseException
     */
    function abort(mixed $code, string $message = '', array $headers = []): void
    {
        if ($code instanceof Responsable) {
            throw new HttpResponseException($code->toResponse(request()));
        }

        app()->abort($code, $message, $headers);
    }
}

if (! function_exists('abort_if')) {
    /**
     * Throw an HttpException with the given data if the given condition is true.
     *
     * @param int|Responsable $code
     *
     * @throws HttpException
     * @throws NotFoundHttpException
     */
    function abort_if(bool $boolean, mixed $code, string $message = '', array $headers = []): void
    {
        if (! $boolean) {
            return;
        }

        abort($code, $message, $headers);
    }
}

if (! function_exists('abort_unless')) {
    /**
     * Throw an HttpException with the given data unless the given condition is true.
     *
     * @param int|Responsable $code
     *
     * @throws HttpException
     * @throws NotFoundHttpException
     */
    function abort_unless(bool $boolean, mixed $code, string $message = '', array $headers = []): void
    {
        if ($boolean) {
            return;
        }

        abort($code, $message, $headers);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     */
    function base_path(string $path = ''): string
    {
        return app()->basePath($path);
    }
}

if (! function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     */
    function app_path(string $path = ''): string
    {
        return base_path("app/{$path}");
    }
}

if (! function_exists('database_path')) {
    /**
     * Get the path to the database folder.
     */
    function database_path(string $path = ''): string
    {
        return base_path("database/{$path}");
    }
}

if (! function_exists('storage_path')) {
    /**
     * Get the path to the storage folder.
     */
    function storage_path(string $path = ''): string
    {
        return base_path("storage/{$path}");
    }
}

if (! function_exists('config_path')) {
    /**
     * Get the configuration path.
     */
    function config_path(string $path = ''): string
    {
        return base_path("config/{$path}");
    }
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param null|array<string, mixed>|string $key
     * @param null|string $default
     * @return ($key is null ? \SwooleTW\Hyperf\Config\Contracts\Repository : ($key is string ? mixed : null))
     */
    function config(mixed $key = null, mixed $default = null): mixed
    {
        return \SwooleTW\Hyperf\Config\config($key, $default);
    }
}

if (! function_exists('cache')) {
    /**
     * Get / set the specified cache value.
     *
     * If an array is passed, we'll assume you want to put to the cache.
     *
     * @param null|array<string, mixed>|string $key key|data
     * @param mixed $default default|expiration|null
     * @return ($key is null ? \SwooleTW\Hyperf\Cache\CacheManager : ($key is string ? mixed : bool))
     *
     * @throws \InvalidArgumentException
     */
    function cache($key = null, $default = null)
    {
        return \SwooleTW\Hyperf\Cache\cache($key, $default);
    }
}

if (! function_exists('cookie')) {
    /**
     * Create a new cookie instance.
     *
     * @return Cookie|CookieContract
     */
    function cookie(string $name, string $value, int $minutes = 0, string $path = '', string $domain = '', bool $secure = false, bool $httpOnly = true, bool $raw = false, ?string $sameSite = null)
    {
        $cookieManager = app(CookieContract::class);
        if (is_null($name)) {
            return $cookieManager;
        }

        return $cookieManager->make($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
}

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @template T
     *
     * @param class-string<T> $abstract
     *
     * @return ContainerInterface|T
     */
    function app(?string $abstract = null, array $parameters = [])
    {
        if (ApplicationContext::hasContainer()) {
            /** @var \Hyperf\Contract\ContainerInterface $container */
            $container = ApplicationContext::getContainer();

            if (is_null($abstract)) {
                return $container;
            }

            if (count($parameters) == 0 && $container->has($abstract)) {
                return $container->get($abstract);
            }

            return $container->make($abstract, $parameters);
        }

        if (is_null($abstract)) {
            throw new \InvalidArgumentException('Invalid argument $abstract');
        }

        return new $abstract(...array_values($parameters));
    }
}

if (! function_exists('dispatch')) {
    /**
     * @param AsyncTaskInterface|Closure|JobInterface|ProduceMessage|ProducerMessageInterface $job
     * @throws TypeError
     * @throws InvalidDriverException
     * @throws InvalidArgumentException
     */
    function dispatch($job, ...$arguments): bool
    {
        if ($job instanceof \Closure) {
            $job = new ClosureJob($job, (int) ($arguments[2] ?? 0));
        }

        return match (true) {
            $job instanceof JobInterface => app(DriverFactory::class)
                ->get((string) ($arguments[0] ?? $job->queue ?? 'default'))
                ->push($job, (int) ($arguments[1] ?? $job->delay ?? 0)),
            $job instanceof AsyncTaskInterface => AsyncTask::deliver($job, ...$arguments),
            default => throw new \InvalidArgumentException('Not Support job type.')
        };
    }
}

if (! function_exists('event')) {
    /**
     * Dispatch an event and call the listeners.
     *
     * @template T of object
     *
     * @param T $event
     *
     * @return T
     */
    function event(object $event)
    {
        return app(EventDispatcherInterface::class)->dispatch($event);
    }
}

if (! function_exists('info')) {
    /**
     * @throws TypeError
     */
    function info(string|Stringable $message, array $context = [], bool $backtrace = false)
    {
        if ($backtrace) {
            $traces = debug_backtrace();
            $context['backtrace'] = sprintf('%s:%s', $traces[0]['file'], $traces[0]['line']);
        }

        return logger()->info($message, $context);
    }
}

if (! function_exists('logger')) {
    /**
     * Log a debug message to the logs.
     *
     * @return null|\SwooleTW\Hyperf\Log\LogManager
     */
    function logger(?string $message = null, array $context = []): ?LoggerInterface
    {
        $logger = app(LoggerInterface::class);
        if (is_null($message)) {
            return $logger;
        }

        $logger->debug($message, $context);

        return null;
    }
}

if (! function_exists('now')) {
    /**
     * Create a new Carbon instance for the current time.
     */
    function now(null|\DateTimeZone|string $tz = null): Carbon
    {
        return Carbon::now($tz);
    }
}

if (! function_exists('resolve')) {
    /**
     * Resolve a service from the container.
     *
     * @template T
     *
     * @param callable|class-string<T> $abstract
     *
     * @return Closure|ContainerInterface|T
     */
    function resolve(callable|string $abstract, array $parameters = [])
    {
        if (is_callable($abstract)) {
            return \Closure::fromCallable($abstract);
        }

        return app($abstract, $parameters);
    }
}

if (! function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     * @return array|mixed|RequestContract
     * @throws TypeError
     */
    function request(null|array|string $key = null, mixed $default = null): mixed
    {
        $request = app(RequestContract::class);

        if (is_null($key)) {
            return $request;
        }

        if (is_array($key)) {
            return $request->inputs($key, value($default));
        }

        return $request->input($key, value($default));
    }
}

if (! function_exists('response')) {
    /**
     * Return a new response from the application.
     */
    function response(mixed $content = '', int $status = 200, array $headers = []): ResponseContract|ResponseInterface
    {
        $response = app(ResponseContract::class);

        if (func_num_args() === 0) {
            return $response;
        }

        return $response->make($content, $status, $headers);
    }
}

if (! function_exists('redirect')) {
    /**
     * Return a new response from the application.
     */
    function redirect(string $toUrl, int $status = 302, string $schema = 'http'): ResponseInterface
    {
        return app(ResponseContract::class)
            ->redirect($toUrl, $status, $schema);
    }
}

if (! function_exists('report')) {
    /**
     * Report an exception.
     */
    function report(string|Throwable $exception): void
    {
        if (is_string($exception)) {
            $exception = new Exception($exception);
        }

        app(ExceptionHandlerContract::class)->report($exception);
    }
}

if (! function_exists('session')) {
    /**
     * Get / set the specified session value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @return mixed|SessionInterface
     */
    function session(null|array|string $key = null, mixed $default = null): mixed
    {
        $session = app(SessionInterface::class);

        if (is_null($key)) {
            return $session;
        }

        if (is_array($key)) {
            return $session->put($key);
        }

        return $session->get($key, $default);
    }
}

if (! function_exists('today')) {
    /**
     * Create a new Carbon instance for the current date.
     *
     * @param null|\DateTimeZone|string $tz
     */
    function today($tz = null): Carbon
    {
        return Carbon::today($tz);
    }
}

if (! function_exists('validator')) {
    /**
     * Create a new Validator instance.
     * @return ValidatorFactoryInterface|ValidatorInterface
     * @throws TypeError
     */
    function validator(array $data = [], array $rules = [], array $messages = [], array $customAttributes = [])
    {
        $factory = app(ValidatorFactoryInterface::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($data, $rules, $messages, $customAttributes);
    }
}

if (! function_exists('route')) {
    /**
     * Get the URL to a named route.
     *
     * @throws InvalidArgumentException
     */
    function route(string $name, array $parameters = [], bool $absolute = true, string $server = 'http'): string
    {
        return \SwooleTW\Hyperf\Router\route($name, $parameters, $absolute, $server);
    }
}

if (! function_exists('url')) {
    /**
     * Generate a url for the application.
     */
    function url(?string $path = null, array $extra = [], ?bool $secure = null): string|UrlGenerator
    {
        return \SwooleTW\Hyperf\Router\url($path, $extra, $secure);
    }
}

if (! function_exists('secure_url')) {
    /**
     * Generate a secure, absolute URL to the given path.
     */
    function secure_url(string $path, array $extra = []): string
    {
        return \SwooleTW\Hyperf\Router\secure_url($path, $extra);
    }
}

if (! function_exists('auth')) {
    /**
     * Get auth guard.
     * @return SwooleTW\Hyperf\Auth\Contracts\FactoryContract|SwooleTW\Hyperf\Auth\Contracts\Guard
     */
    function auth(?string $guard = null): mixed
    {
        return \SwooleTW\Hyperf\Auth\auth($guard);
    }
}

if (! function_exists('trans')) {
    function trans(string $key, array $replace = [], ?string $locale = null)
    {
        return \Hyperf\Translation\trans($key, $replace, $locale);
    }
}

if (! function_exists('trans_choice')) {
    function trans_choice(string $key, $number, array $replace = [], ?string $locale = null): string
    {
        return \Hyperf\Translation\trans_choice($key, $number, $replace, $locale);
    }
}

if (! function_exists('__')) {
    function __(string $key, array $replace = [], ?string $locale = null)
    {
        return \Hyperf\Translation\trans($key, $replace, $locale);
    }
}

if (! function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param null|string $view
     * @param array $mergeData
     */
    function view($view = null, array|Arrayable $data = [], $mergeData = []): FactoryInterface|ViewInterface
    {
        $factory = app(FactoryInterface::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}
