<?php

declare(strict_types=1);

use Carbon\Carbon;
use FriendsOfHyperf\AsyncTask\Task as AsyncTask;
use FriendsOfHyperf\AsyncTask\TaskInterface as AsyncTaskInterface;
use FriendsOfHyperf\Support\AsyncQueue\ClosureJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\SessionInterface;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Log\LoggerInterface;
use SwooleTW\Hyperf\Cache\Contracts\Factory as CacheManager;
use SwooleTW\Hyperf\Cookie\Contracts\Cookie as CookieContract;

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     */
    function base_path(string $path = ''): string
    {
        return BASE_PATH . ($path ? '/' . $path : $path);
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

if (! function_exists('config')) {
    /**
     * Get config value.
     *
     * @param null|string $default
     */
    function config(string $key, mixed $default = null): mixed
    {
        return \Hyperf\Config\config($key, $default);
    }
}

if (! function_exists('cache')) {
    /**
     * Get / set the specified cache value.
     *
     * If an array is passed, we'll assume you want to put to the cache.
     *
     * @param  dynamic  key|key,default|data,expiration|null
     * @return mixed|\SwooleTW\Hyperf\Cache\Contracts\Repository
     * @throws Exception
     */
    function cache()
    {
        $arguments = func_get_args();
        $manager = app(CacheManager::class);

        if (empty($arguments)) {
            return $manager;
        }

        if (is_string($arguments[0])) {
            return $manager->get(...$arguments);
        }

        if (! is_array($arguments[0])) {
            throw new \InvalidArgumentException(
                'When setting a value in the cache, you must pass an array of key / value pairs.'
            );
        }

        return $manager->put(key($arguments[0]), reset($arguments[0]), $arguments[1] ?? null);
    }
}

if (! function_exists('cookie')) {
    /**
     * Create a new cookie instance.
     *
     * @return Cookie|CookieContract
     */
    function cookie(?string $name = null, string $value = null, int $minutes = 0, string $path = null, string $domain = null, bool $secure = false, bool $httpOnly = true, bool $raw = false, ?string $sameSite = null)
    {
        if (is_null($name)) {
            return app(CookieContract::class);
        }

        $time = ($minutes == 0) ? 0 : $minutes * 60;

        return new Cookie($name, $value, $time, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
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
    function app(string $abstract = null, array $parameters = [])
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
     * @return bool
     * @throws TypeError
     * @throws InvalidDriverException
     * @throws InvalidArgumentException
     */
    function dispatch($job, ...$arguments)
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
     * @param string|Stringable $message
     * @throws TypeError
     */
    function info($message, array $context = [], bool $backtrace = false)
    {
        if ($backtrace) {
            $traces = debug_backtrace();
            $context['backtrace'] = sprintf('%s:%s', $traces[0]['file'], $traces[0]['line']);
        }

        return logs()->info($message, $context);
    }
}

if (! function_exists('logger')) {
    /**
     * Log a debug message to the logs.
     *
     * @param null|string $message
     * @return null|\SwooleTW\Hyperf\Log\LogManager
     */
    function logger($message = null, array $context = [])
    {
        $logger = app(LoggerInterface::class);
        if (is_null($message)) {
            return $logger;
        }

        return $logger->debug($message, $context);
    }
}

if (! function_exists('now')) {
    /**
     * Create a new Carbon instance for the current time.
     *
     * @param null|DateTimeZone|string $tz
     */
    function now($tz = null): Carbon
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
    function resolve(string|callable $abstract, array $parameters = [])
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
     * @param null|array|string $key
     * @param mixed $default
     * @return array|mixed|RequestInterface
     * @throws TypeError
     */
    function request($key = null, $default = null)
    {
        $request = app(RequestInterface::class);

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
     *
     * @param null|array|string $content
     * @param int $status
     * @return PsrResponseInterface|ResponseInterface
     */
    function response($content = '', $status = 200, array $headers = [])
    {
        /** @var PsrResponseInterface|ResponseInterface $response */
        $response = app(ResponseInterface::class);

        if (func_num_args() === 0) {
            return $response;
        }

        if (is_array($content)) {
            $response->withAddedHeader('Content-Type', 'application/json');
            $content = json_encode($content);
        }

        return tap(
            $response->withBody(new SwooleStream((string) $content))
                ->withStatus($status),
            function ($response) use ($headers) {
                foreach ($headers as $name => $value) {
                    $response->withAddedHeader($name, $value);
                }
            }
        );
    }
}

if (! function_exists('session')) {
    /**
     * Get / set the specified session value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param null|array|string $key
     * @param mixed $default
     * @return mixed|SessionInterface
     */
    function session($key = null, $default = null)
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
    function route(string $name, array $parameters = [], string $server = 'http'): string
    {
        return \SwooleTW\Hyperf\Router\route($name, $parameters, $server);
    }
}

if (! function_exists('url')) {
    /**
     * Generate a url for the application.
     */
    function url(string $path, array $extra = [], bool $secure = null): string
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
