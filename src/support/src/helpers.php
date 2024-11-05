<?php

declare(strict_types=1);

use Hyperf\Collection\Collection;
use Hyperf\Context\ApplicationContext;
use Hyperf\Tappable\HigherOrderTapProxy;
use SwooleTW\Hyperf\Support\Environment;

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     */
    function value(mixed $value, mixed ...$args)
    {
        return \Hyperf\Support\value($value, ...$args);
    }
}

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     */
    function env(string $key, mixed $default = null): mixed
    {
        return \Hyperf\Support\env($key, $default);
    }
}

if (! function_exists('environment')) {
    /**
     * @throws TypeError
     */
    function environment(mixed ...$environments): bool|Environment
    {
        $environment = ApplicationContext::hasContainer()
            ? ApplicationContext::getContainer()
                ->get(Environment::class)
            : new Environment();

        if (count($environments) > 0) {
            return $environment->is(...$environments);
        }

        return $environment;
    }
}

if (! function_exists('blank')) {
    /**
     * Determine if the given value is "blank".
     */
    function blank(mixed $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if ($value instanceof \Countable) {
            return count($value) === 0;
        }

        return empty($value);
    }
}

if (! function_exists('collect')) {
    /**
     * Create a collection from the given value.
     */
    function collect(mixed $value = null): Collection
    {
        return new Collection($value);
    }
}

if (! function_exists('data_fill')) {
    /**
     * Fill in data where it's missing.
     */
    function data_fill(mixed &$target, array|string $key, mixed $value): mixed
    {
        return \Hyperf\Collection\data_set($target, $key, $value, false);
    }
}

if (! function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     */
    function data_get(mixed $target, null|array|int|string $key, mixed $default = null): mixed
    {
        return \Hyperf\Collection\data_get($target, $key, $default);
    }
}

if (! function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     */
    function data_set(mixed &$target, array|string $key, mixed $value, bool $overwrite = true): mixed
    {
        return \Hyperf\Collection\data_set($target, $key, $value, $overwrite);
    }
}

if (! function_exists('data_forget')) {
    /**
     * Remove / unset an item from an array or object using "dot" notation.
     */
    function data_forget(mixed &$target, null|array|int|string $key): mixed
    {
        return \Hyperf\Collection\data_forget($target, $key);
    }
}

if (! function_exists('head')) {
    /**
     * Get the first element of an array. Useful for method chaining.
     */
    function head(array $array): mixed
    {
        return reset($array);
    }
}

if (! function_exists('last')) {
    /**
     * Get the last element from an array.
     */
    function last(array $array): mixed
    {
        return end($array);
    }
}

if (! function_exists('filled')) {
    /**
     * Determine if a value is "filled".
     */
    function filled(mixed $value): bool
    {
        return ! blank($value);
    }
}

if (! function_exists('class_basename')) {
    /**
     * Get the class "basename" of the given object / class.
     */
    function class_basename(object|string $class): string
    {
        return \Hyperf\Support\class_basename($class);
    }
}

if (! function_exists('class_uses_recursive')) {
    /**
     * Returns all traits used by a class, its parent classes and trait of their traits.
     */
    function class_uses_recursive(object|string $class): array
    {
        return \Hyperf\Support\class_uses_recursive($class);
    }
}

if (! function_exists('object_get')) {
    /**
     * Get an item from an object using "dot" notation.
     */
    function object_get(object $object, ?string $key, mixed $default = null): mixed
    {
        if (is_null($key) || trim($key) === '') {
            return $object;
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_object($object) || ! isset($object->{$segment})) {
                return value($default);
            }

            $object = $object->{$segment};
        }

        return $object;
    }
}

if (! function_exists('optional')) {
    /**
     * Provide access to optional objects.
     */
    function optional(mixed $value = null, ?callable $callback = null): mixed
    {
        return \Hyperf\Support\optional($value, $callback);
    }
}

if (! function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @throws Throwable
     */
    function retry(float|int $times, callable $callback, int $sleep = 0)
    {
        return \Hyperf\Support\retry($times, $callback, $sleep);
    }
}

if (! function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     */
    function tap(mixed $value, ?callable $callback = null): mixed
    {
        if (is_null($callback)) {
            return new HigherOrderTapProxy($value);
        }

        $callback($value);

        return $value;
    }
}

if (! function_exists('trait_uses_recursive')) {
    /**
     * Returns all traits used by a trait and its traits.
     */
    function trait_uses_recursive(object|string $trait): array
    {
        $traits = class_uses($trait) ?: [];

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}

if (! function_exists('transform')) {
    /**
     * Transform the given value if it is present.
     */
    function transform(mixed $value, callable $callback, mixed $default = null): mixed
    {
        if (filled($value)) {
            return $callback($value);
        }

        if (is_callable($default)) {
            return $default($value);
        }

        return $default;
    }
}

if (! function_exists('with')) {
    /**
     * Return the given value, optionally passed through the given callback.
     */
    function with(mixed $value, ?callable $callback = null): mixed
    {
        return \Hyperf\Support\with($value, $callback);
    }
}

if (! function_exists('throw_if')) {
    /**
     * Throw the given exception if the given condition is true.
     *
     * @template T
     *
     * @param T $condition
     * @param string|Throwable $exception
     * @param array ...$parameters
     * @return T
     * @throws Throwable
     */
    function throw_if($condition, $exception, ...$parameters)
    {
        if ($condition) {
            if (is_string($exception) && class_exists($exception)) {
                $exception = new $exception(...$parameters);
            }

            throw is_string($exception) ? new \RuntimeException($exception) : $exception;
        }

        return $condition;
    }
}

if (! function_exists('throw_unless')) {
    /**
     * Throw the given exception unless the given condition is true.
     *
     * @template T
     *
     * @param T $condition
     * @param string|Throwable $exception
     * @param array ...$parameters
     * @return T
     * @throws Throwable
     */
    function throw_unless($condition, $exception, ...$parameters)
    {
        if (! $condition) {
            if (is_string($exception) && class_exists($exception)) {
                $exception = new $exception(...$parameters);
            }

            throw is_string($exception) ? new \RuntimeException($exception) : $exception;
        }

        return $condition;
    }
}

if (! function_exists('when')) {
    /**
     * @param mixed $expr
     * @param mixed $value
     * @param mixed $default
     * @return mixed
     */
    function when($expr, $value = null, $default = null)
    {
        $result = value($expr) ? $value : $default;

        return $result instanceof \Closure ? $result($expr) : $result;
    }
}
