<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Contracts\Container;

use Closure;
use Hyperf\Contract\ContainerInterface as HyperfContainerInterface;

interface Container extends HyperfContainerInterface
{
    /**
     * Unbind an arbitrary resolved entry.
     */
    public function unbind(string $name): void;

    /**
     * Determine if the given abstract type has been bound.
     *
     * @param  string  $abstract
     * @return bool
     */
    public function bound(string $abstract): bool;

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     * `has($name)` returning true does not mean that `get($name)` will not throw an exception.
     * It does however mean that `get($name)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param mixed|string $id identifier of the entry to look for
     */
    public function has($id): bool;

    /**
     * Determine if the given abstract type has been resolved.
     *
     * @param  string  $abstract
     * @return bool
     */
    public function resolved(string $abstract): bool;

    /**
     * Determine if a given string is an alias.
     *
     * @param  string  $name
     * @return bool
     */
    public function isAlias(string $name): bool;

    /**
     * Register a binding with the container.
     *
     * @param  string  $abstract
     * @param  \Closure|string|null  $concrete
     * @param  bool  $shared
     * @return void
     *
     * @throws \TypeError
     */
    public function bind(string $abstract, mixed $concrete = null): void;

    /**
     * "Extend" an abstract type in the container.
     *
     * @param  string  $abstract
     * @param  \Closure  $closure
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function extend(string $abstract, Closure $closure): void;

    /**
     * Register an existing instance as shared in the container.
     *
     * @param  string  $abstract
     * @param  mixed  $instance
     * @return mixed
     */
    public function instance(string $abstract, mixed $instance): mixed;

    /**
     * Alias a type to a different name.
     *
     * @param  string  $abstract
     * @param  string  $alias
     * @return void
     *
     * @throws \LogicException
     */
    public function alias(string $abstract, string $alias): void;

    /**
     * Bind a new callback to an abstract's rebind event.
     *
     * @param  string  $abstract
     * @param  \Closure  $callback
     * @return mixed
     */
    public function rebinding(string $abstract, Closure $callback): mixed;

    /**
     * Refresh an instance on the given target and method.
     *
     * @param  string  $abstract
     * @param  mixed  $target
     * @param  string  $method
     * @return mixed
     */
    public function refresh(string $abstract, mixed $target, string $method): mixed;

    /**
     * An alias function name for make().
     *
     * @param  string|callable  $abstract
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function makeWith($abstract, array $parameters = []): mixed;

    /**
     * Register a new before resolving callback for all types.
     *
     * @param  \Closure|string  $abstract
     * @param  \Closure|null  $callback
     * @return void
     */
    public function beforeResolving($abstract, Closure $callback = null): void;

    /**
     * Register a new resolving callback.
     *
     * @param  \Closure|string  $abstract
     * @param  \Closure|null  $callback
     * @return void
     */
    public function resolving($abstract, Closure $callback = null): void;

    /**
     * Register a new after resolving callback for all types.
     *
     * @param  \Closure|string  $abstract
     * @param  \Closure|null  $callback
     * @return void
     */
    public function afterResolving($abstract, Closure $callback = null): void;

    /**
     * Get the container's bindings.
     *
     * @return array
     */
    public function getBindings(): array;

    /**
     * Get the alias for an abstract if available.
     *
     * @param  string  $abstract
     * @return string
     */
    public function getAlias(string $abstract): string;

    /**
     * Remove all of the extender callbacks for a given type.
     *
     * @param  string  $abstract
     * @return void
     */
    public function forgetExtenders(string $abstract): void;

    /**
     * Remove a resolved instance from the instance cache.
     *
     * @param  string  $abstract
     * @return void
     */
    public function forgetInstance(string $abstract): void;

    /**
     * Clear all of the instances from the container.
     *
     * @return void
     */
    public function forgetInstances(): void;

    /**
     * Flush the container of all bindings and resolved instances.
     *
     * @return void
     */
    public function flush(): void;

    /**
     * Get the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance(): static;

    /**
     * Set the shared instance of the container.
     *
     * @param  \SwooleTW\Hyperf\Contracts\Container\Container|null  $container
     * @return \SwooleTW\Hyperf\Contracts\Container\Container|static
     */
    public static function setInstance(Container $container = null): static;
}
